import { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from '@inertiajs/react';

export default function Play({ modality, prefilledNumbers = [], historyItem = null, latestDraw = null }) {
    const [count, setCount] = useState(
        prefilledNumbers.length > 0 ? prefilledNumbers.length : modality.bet_min_count
    );
    const [numbers, setNumbers] = useState(prefilledNumbers);
    const [manualNumbers, setManualNumbers] = useState(
        prefilledNumbers.length > 0 ? prefilledNumbers.join(', ') : ''
    );
    const [analysis, setAnalysis] = useState(null);
    const [loadingGenerate, setLoadingGenerate] = useState(false);
    const [loadingAnalyze, setLoadingAnalyze] = useState(false);
    const [loadingRegisterBet, setLoadingRegisterBet] = useState(false);
    const [error, setError] = useState('');
    const [successMessage, setSuccessMessage] = useState('');
    const [smartStrategy, setSmartStrategy] = useState('balanced');
    const [smartGamesCount, setSmartGamesCount] = useState(5);
    const [loadingSmartGenerate, setLoadingSmartGenerate] = useState(false);
    const [smartGames, setSmartGames] = useState([]);
    const [currentSource, setCurrentSource] = useState(
        historyItem?.source || (prefilledNumbers.length > 0 ? 'manual' : 'manual')
    );
    const [currentHistoryId, setCurrentHistoryId] = useState(historyItem?.id || null);
    const [betContestNumber, setBetContestNumber] = useState(historyItem?.bet_contest_number || null);
    const [betResultSnapshot, setBetResultSnapshot] = useState(historyItem?.bet_result_snapshot || null);

    const quinaBlue = '#0c5a96';
    const quinaBall = '#0f4c81';
    const quinaBorder = '#d9e1ea';

    const parsedManualNumbers = useMemo(() => {
        return manualNumbers
            .split(/[\s,;]+/)
            .map((value) => value.trim())
            .filter(Boolean)
            .map((value) => Number(value))
            .filter((value) => Number.isInteger(value));
    }, [manualNumbers]);

    useEffect(() => {
        if (prefilledNumbers.length > 0) {
            analyzeNumbers(prefilledNumbers, historyItem?.source || 'manual');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    async function handleGenerate() {
        setLoadingGenerate(true);
        setError('');
        setSuccessMessage('');

        try {
            const response = await axios.post(
                `/lottery/modalities/${modality.id}/generate`,
                { count }
            );

            const generatedNumbers = response.data.numbers || [];

            setNumbers(generatedNumbers);
            setManualNumbers(generatedNumbers.join(', '));
            setCurrentSource('generated');
            setCurrentHistoryId(null);
            setBetContestNumber(null);
            setBetResultSnapshot(null);
            await analyzeNumbers(generatedNumbers, 'generated');
        } catch (err) {
            setError(err?.response?.data?.message || 'Erro ao gerar jogo.');
        } finally {
            setLoadingGenerate(false);
        }
    }

    async function analyzeNumbers(targetNumbers, source = 'manual') {
        setLoadingAnalyze(true);
        setError('');
        setSuccessMessage('');

        try {
            const response = await axios.post(
                `/lottery/modalities/${modality.id}/analyze`,
                { numbers: targetNumbers, source }
            );

            setAnalysis(response.data);
            setNumbers(targetNumbers);
            setManualNumbers(targetNumbers.join(', '));
            setCurrentSource(source);
            setCurrentHistoryId(response.data.history_item_id || null);
            setBetContestNumber(response.data.bet_contest_number || null);
            setBetResultSnapshot(null);
            return response.data;
        } catch (err) {
            setAnalysis(null);
            setError(err?.response?.data?.message || 'Erro ao analisar jogo.');
            return null;
        } finally {
            setLoadingAnalyze(false);
        }
    }

    async function handleAnalyzeManual() {
        setCurrentSource('manual');
        setCurrentHistoryId(null);
        setBetContestNumber(null);
        setBetResultSnapshot(null);
        await analyzeNumbers(parsedManualNumbers, 'manual');
    }

    async function handleRegisterBet() {
        setLoadingRegisterBet(true);
        setError('');
        setSuccessMessage('');

        try {
            let targetHistoryId = currentHistoryId;

            if (!targetHistoryId) {
                const analysisResponse = await analyzeNumbers(numbers, currentSource || 'manual');
                targetHistoryId = analysisResponse?.history_item_id || null;
            }

            if (!targetHistoryId) {
                throw new Error('Analise a combinação antes de registrar a aposta.');
            }

            const response = await axios.post(
                `/lottery/modalities/${modality.id}/combination-history/${targetHistoryId}/register-bet`
            );

            setCurrentHistoryId(response.data.item?.id || targetHistoryId);
            setBetContestNumber(response.data.item?.bet_contest_number || null);
            setBetResultSnapshot(response.data.item?.bet_result_snapshot || null);
            setSuccessMessage(response.data.message || 'Aposta registrada com sucesso.');
        } catch (err) {
            setError(
                err?.response?.data?.message ||
                err?.message ||
                'Não foi possível registrar a aposta.'
            );
        } finally {
            setLoadingRegisterBet(false);
        }
    }

    return (
        <div className="max-w-6xl mx-auto p-6 space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <Link
                    href={`/lottery/modalities/${modality.id}/combination-history`}
                    className="px-4 py-2 rounded border"
                >
                    Ver histórico
                </Link>

                <Link
                    href={`/lottery/modalities/${modality.id}`}
                    className="px-4 py-2 rounded border"
                >
                    Voltar
                </Link>
            </div>

            <div>
                <h1 style={{ color: quinaBlue, fontSize: 34, fontWeight: 800 }}>
                    Gerador e análise — {modality.name}
                </h1>

                <p className="text-slate-500 mt-1">
                    Números válidos: {modality.min_number} a {modality.max_number}
                </p>
            </div>

            {latestDraw ? (
                <section
                    className="p-5 rounded-2xl border bg-white space-y-4"
                    style={{ borderColor: quinaBorder }}
                >
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                                Concurso atual disponível
                            </h2>
                            <p className="text-slate-600 mt-1">
                                Concurso {latestDraw.contest_number}
                                {latestDraw.draw_date ? ` • resultado oficial de ${latestDraw.draw_date}` : ''}
                            </p>
                        </div>

                        {betContestNumber ? (
                            <Link
                                href={`/lottery/modalities/${modality.id}/combination-history/${currentHistoryId}/check-bet`}
                                className="px-4 py-3 rounded-xl border text-center font-semibold"
                                style={{ borderColor: quinaBlue, color: quinaBlue }}
                            >
                                Conferir aposta
                            </Link>
                        ) : (
                            <button
                                type="button"
                                onClick={handleRegisterBet}
                                disabled={loadingRegisterBet || numbers.length === 0}
                                className="px-4 py-3 rounded-xl text-center font-semibold"
                                style={{
                                    backgroundColor: quinaBlue,
                                    color: '#fff',
                                    opacity: loadingRegisterBet || numbers.length === 0 ? 0.7 : 1,
                                }}
                            >
                                {loadingRegisterBet
                                    ? 'Registrando aposta...'
                                    : `Realizar aposta no concurso ${latestDraw.contest_number}`}
                            </button>
                        )}
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {latestDraw.numbers.map((number) => (
                            <div
                                key={number}
                                style={{
                                    width: 42,
                                    height: 42,
                                    borderRadius: '9999px',
                                    backgroundColor: quinaBall,
                                    color: '#fff',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    fontWeight: 700,
                                }}
                            >
                                {String(number).padStart(2, '0')}
                            </div>
                        ))}
                    </div>

                    {betContestNumber ? (
                        <div className="rounded-xl border px-4 py-3 text-sm" style={{ borderColor: '#abefc6', backgroundColor: '#ecfdf3', color: '#067647' }}>
                            Sua combinação atual está vinculada ao concurso {betContestNumber}.
                            {betResultSnapshot?.hit_label ? ` Última conferência: ${betResultSnapshot.hit_label}.` : ''}
                        </div>
                    ) : null}
                </section>
            ) : null}

            {error ? (
                <div className="p-3 rounded border border-red-300 bg-red-50 text-red-700">
                    {error}
                </div>
            ) : null}

            {successMessage ? (
                <div className="p-3 rounded border border-green-300 bg-green-50 text-green-700">
                    {successMessage}
                </div>
            ) : null}

            <div className="grid md:grid-cols-2 gap-6">
                <section className="p-5 rounded-2xl border bg-white space-y-4">
                    <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                        Gerar combinação
                    </h2>

                    <div>
                        <label className="block text-sm mb-1">Quantidade de números</label>
                        <input
                            type="number"
                            min={modality.bet_min_count}
                            max={modality.bet_max_count}
                            value={count}
                            onChange={(e) => setCount(Number(e.target.value))}
                            className="w-full rounded-xl border px-3 py-2"
                        />
                    </div>

                    <button
                        onClick={handleGenerate}
                        disabled={loadingGenerate}
                        style={{
                            backgroundColor: quinaBlue,
                            color: '#fff',
                            padding: '12px 18px',
                            borderRadius: 12,
                            fontWeight: 700,
                            opacity: loadingGenerate ? 0.7 : 1,
                        }}
                    >
                        {loadingGenerate ? 'Gerando...' : 'Gerar jogo'}
                    </button>

                    <div className="pt-4 border-t space-y-3">
                        <h3 className="font-semibold text-slate-700">Gerador inteligente</h3>

                        <div>
                            <label className="block text-sm mb-1">Estratégia</label>
                            <select
                                value={smartStrategy}
                                onChange={(e) => setSmartStrategy(e.target.value)}
                                className="w-full rounded-xl border px-3 py-2"
                            >
                                <option value="balanced">Equilibrado</option>
                                <option value="hot">Quente</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm mb-1">Quantidade de jogos</label>
                            <input
                                type="number"
                                min={1}
                                max={10}
                                value={smartGamesCount}
                                onChange={(e) => setSmartGamesCount(Number(e.target.value))}
                                className="w-full rounded-xl border px-3 py-2"
                            />
                        </div>

                        <button
                            onClick={async () => {
                                setLoadingSmartGenerate(true);
                                setError('');
                                setSuccessMessage('');

                                try {
                                    const response = await axios.post(
                                        `/lottery/modalities/${modality.id}/generate-smart`,
                                        {
                                            strategy: smartStrategy,
                                            games: smartGamesCount,
                                        }
                                    );

                                    setSmartGames(response.data.games || []);
                                } catch (err) {
                                    setSmartGames([]);
                                    setError(err?.response?.data?.message || 'Erro ao gerar jogos inteligentes.');
                                } finally {
                                    setLoadingSmartGenerate(false);
                                }
                            }}
                            disabled={loadingSmartGenerate}
                            className="px-4 py-2 rounded-xl border"
                        >
                            {loadingSmartGenerate ? 'Gerando inteligentes...' : 'Gerar jogos inteligentes'}
                        </button>
                    </div>
                </section>

                <section className="p-5 rounded-2xl border bg-white space-y-4">
                    <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                        Analisar combinação manual
                    </h2>

                    <div>
                        <label className="block text-sm mb-1">
                            Digite os números separados por vírgula ou espaço
                        </label>
                        <input
                            type="text"
                            value={manualNumbers}
                            onChange={(e) => {
                                setManualNumbers(e.target.value);
                                setCurrentSource('manual');
                                setCurrentHistoryId(null);
                                setBetContestNumber(null);
                                setBetResultSnapshot(null);
                            }}
                            placeholder="Ex: 1, 7, 22, 33, 80"
                            className="w-full rounded-xl border px-3 py-2"
                        />
                    </div>

                    <button
                        onClick={handleAnalyzeManual}
                        disabled={loadingAnalyze}
                        className="px-4 py-2 rounded-xl border"
                    >
                        {loadingAnalyze ? 'Analisando...' : 'Analisar'}
                    </button>
                </section>
            </div>

            {smartGames.length > 0 ? (
                <section className="p-5 rounded-2xl border bg-white space-y-4">
                    <div className="flex items-center justify-between gap-3">
                        <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                            Jogos inteligentes sugeridos
                        </h2>
                        <span className="text-sm text-slate-500">
                            Estratégia: {smartStrategy === 'hot' ? 'Quente' : 'Equilibrado'}
                        </span>
                    </div>

                    <div className="grid lg:grid-cols-2 gap-4">
                        {smartGames.map((game, index) => (
                            <div key={`${game.numbers.join('-')}-${index}`} className="rounded-2xl border p-4 space-y-3">
                                <div className="flex flex-wrap gap-2">
                                    {game.numbers.map((number) => (
                                        <div
                                            key={number}
                                            style={{
                                                width: 42,
                                                height: 42,
                                                borderRadius: '9999px',
                                                backgroundColor: quinaBall,
                                                color: '#fff',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                                fontWeight: 700,
                                            }}
                                        >
                                            {String(number).padStart(2, '0')}
                                        </div>
                                    ))}
                                </div>

                                <div className="grid grid-cols-2 gap-2 text-sm">
                                    <div>Score: <strong>{game.weighted_score}</strong></div>
                                    <div>Perfil: <strong>{game.profile}</strong></div>
                                    <div>Classificação: <strong>{game.classification}</strong></div>
                                    <div>Frequentes: <strong>{game.top_frequency_hits}</strong></div>
                                </div>

                                <p className="text-sm text-slate-600">{game.reason}</p>

                                <div className="flex flex-wrap gap-2">
                                    <button
                                        className="px-3 py-2 rounded-xl border"
                                        onClick={() => {
                                            setNumbers(game.numbers);
                                            setManualNumbers(game.numbers.join(', '));
                                            setCurrentSource('generated');
                                            setCurrentHistoryId(null);
                                            setBetContestNumber(null);
                                            setBetResultSnapshot(null);
                                            setSuccessMessage('');
                                        }}
                                    >
                                        Usar esta combinação
                                    </button>

                                    <button
                                        className="px-3 py-2 rounded-xl border"
                                        onClick={() => analyzeNumbers(game.numbers, 'generated')}
                                    >
                                        Analisar agora
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>
            ) : null}

            <section className="p-5 rounded-2xl border bg-white space-y-4">
                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                        Combinação atual
                    </h2>

                    <div className="flex flex-wrap gap-2">
                        {betContestNumber && currentHistoryId ? (
                            <Link
                                href={`/lottery/modalities/${modality.id}/combination-history/${currentHistoryId}/check-bet`}
                                className="px-4 py-2 rounded-xl border text-sm font-semibold"
                                style={{ borderColor: quinaBlue, color: quinaBlue }}
                            >
                                Conferir jogo
                            </Link>
                        ) : null}

                        <button
                            type="button"
                            onClick={handleRegisterBet}
                            disabled={loadingRegisterBet || numbers.length === 0 || !latestDraw}
                            className="px-4 py-2 rounded-xl text-sm font-semibold"
                            style={{
                                backgroundColor: quinaBlue,
                                color: '#fff',
                                opacity: loadingRegisterBet || numbers.length === 0 || !latestDraw ? 0.7 : 1,
                            }}
                        >
                            {loadingRegisterBet
                                ? 'Registrando...'
                                : latestDraw
                                    ? `Realizar aposta no concurso ${latestDraw.contest_number}`
                                    : 'Realizar aposta'}
                        </button>
                    </div>
                </div>

                <div className="flex flex-wrap gap-3">
                    {numbers.length > 0 ? (
                        numbers.map((number) => (
                            <div
                                key={number}
                                style={{
                                    width: 54,
                                    height: 54,
                                    borderRadius: '9999px',
                                    backgroundColor: quinaBall,
                                    color: '#fff',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    fontWeight: 700,
                                    fontSize: 24,
                                }}
                            >
                                {String(number).padStart(2, '0')}
                            </div>
                        ))
                    ) : (
                        <span className="text-sm text-gray-500">
                            Nenhuma combinação selecionada ainda.
                        </span>
                    )}
                </div>

                {betContestNumber ? (
                    <div className="rounded-xl border px-4 py-3 text-sm" style={{ borderColor: '#abefc6', backgroundColor: '#ecfdf3', color: '#067647' }}>
                        A combinação atual foi vinculada ao concurso {betContestNumber}.
                    </div>
                ) : null}
            </section>

            {analysis ? (
                <div className="grid lg:grid-cols-2 gap-6">
                    <section className="p-5 rounded-2xl border bg-white space-y-3">
                        <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                            Resumo da combinação
                        </h2>

                        <div>Soma: <strong>{analysis.sum}</strong></div>
                        <div>Pares: <strong>{analysis.even_count}</strong></div>
                        <div>Ímpares: <strong>{analysis.odd_count}</strong></div>
                        <div>Menor número: <strong>{analysis.min}</strong></div>
                        <div>Maior número: <strong>{analysis.max}</strong></div>
                        <div>Amplitude: <strong>{analysis.range}</strong></div>
                    </section>

                    <section className="p-5 rounded-2xl border bg-white space-y-3">
                        <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                            Indicadores históricos
                        </h2>

                        <div>Média de frequência: <strong>{analysis.average_frequency}</strong></div>
                        <div>Média de atraso: <strong>{analysis.average_delay}</strong></div>
                        <div>Acertos entre mais frequentes: <strong>{analysis.top_frequency_hits}</strong></div>
                        <div>Acertos entre mais atrasados: <strong>{analysis.top_delay_hits}</strong></div>
                    </section>

                    <section className="p-5 rounded-2xl border bg-white space-y-3">
                        <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                            Faixas
                        </h2>

                        {Object.entries(analysis.range_distribution || {}).map(([label, total]) => (
                            <div key={label} className="flex justify-between border-b py-1">
                                <span>{label}</span>
                                <strong>{total}</strong>
                            </div>
                        ))}
                    </section>

                    <section className="p-5 rounded-2xl border bg-white space-y-3">
                        <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                            Sequências consecutivas
                        </h2>

                        {analysis.consecutive_count > 0 ? (
                            <div className="space-y-2">
                                {analysis.consecutive_groups?.map((group, index) => (
                                    <div key={index} className="flex gap-2 flex-wrap">
                                        {group.map((number) => (
                                            <div
                                                key={number}
                                                style={{
                                                    width: 42,
                                                    height: 42,
                                                    borderRadius: '9999px',
                                                    backgroundColor: '#e8f1f8',
                                                    color: quinaBlue,
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    justifyContent: 'center',
                                                    fontWeight: 700,
                                                }}
                                            >
                                                {number}
                                            </div>
                                        ))}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <span className="text-sm text-gray-500">
                                Nenhuma sequência consecutiva encontrada.
                            </span>
                        )}
                    </section>

                    <section className="p-5 rounded-2xl border bg-white space-y-4 lg:col-span-2">
                        <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                            Comparação com histórico
                        </h2>

                        <div className="grid md:grid-cols-2 gap-6">
                            <div className="space-y-2">
                                <div>
                                    Soma da combinação: <strong>{analysis.sum}</strong>
                                </div>
                                <div>
                                    Média histórica da soma:{' '}
                                    <strong>{analysis.historical_averages?.sum}</strong>
                                </div>
                                <div>
                                    Diferença da soma:{' '}
                                    <strong>{analysis.historical_comparison?.sum_diff}</strong>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <div>
                                    Pares na combinação: <strong>{analysis.even_count}</strong>
                                </div>
                                <div>
                                    Média histórica de pares:{' '}
                                    <strong>{analysis.historical_averages?.even_count}</strong>
                                </div>
                                <div>
                                    Padrão mais comum de pares:{' '}
                                    <strong>{analysis.most_common_even_count}</strong>
                                </div>
                                <div>
                                    Está no padrão mais comum:{' '}
                                    <strong>{analysis.within_common_even_pattern ? 'Sim' : 'Não'}</strong>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <div>
                                    Amplitude da combinação: <strong>{analysis.range}</strong>
                                </div>
                                <div>
                                    Média histórica da amplitude:{' '}
                                    <strong>{analysis.historical_averages?.range}</strong>
                                </div>
                                <div>
                                    Diferença da amplitude:{' '}
                                    <strong>{analysis.historical_comparison?.range_diff}</strong>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <div>
                                    Diferença de pares vs histórico:{' '}
                                    <strong>{analysis.historical_comparison?.even_count_diff}</strong>
                                </div>
                                <div className="text-sm text-slate-500">
                                    Valores positivos indicam combinação acima da média histórica.
                                    Valores negativos indicam abaixo da média.
                                </div>
                            </div>
                        </div>
                    </section>

                    <section className="p-5 rounded-2xl border bg-white space-y-4 lg:col-span-2">
                        <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                            Leitura automática da combinação
                        </h2>

                        <div
                            style={{
                                backgroundColor: '#eef6ff',
                                border: '1px solid #d6e8fb',
                                borderRadius: 16,
                                padding: 16,
                            }}
                        >
                            <div style={{ color: quinaBlue, fontSize: 20, fontWeight: 700 }}>
                                {analysis.narrative?.headline}
                            </div>
                        </div>

                        <div className="space-y-2">
                            {analysis.narrative?.insights?.map((item, index) => (
                                <div
                                    key={index}
                                    className="rounded-xl border px-4 py-3"
                                    style={{ borderColor: '#dbe4ee', backgroundColor: '#fafcff' }}
                                >
                                    {item}
                                </div>
                            ))}
                        </div>
                    </section>

                    {analysis?.agent ? (
                        <section className="p-5 rounded-2xl border bg-white space-y-4 lg:col-span-2">
                            <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                                Agente analista da combinação
                            </h2>

                            <div
                                style={{
                                    backgroundColor: '#eef6ff',
                                    border: '1px solid #d6e8fb',
                                    borderRadius: 16,
                                    padding: 16,
                                }}
                            >
                                <div style={{ color: quinaBlue, fontSize: 20, fontWeight: 700 }}>
                                    {analysis.agent.summary}
                                </div>
                            </div>
                        </section>
                    ) : null}
                </div>
            ) : null}
        </div>
    );
}

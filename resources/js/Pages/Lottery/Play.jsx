import { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, usePage } from '@inertiajs/react';

function NumberBall({ number, active = true }) {
    const quinaBlue = '#0c5a96';

    return (
        <div
            style={{
                width: 48,
                height: 48,
                borderRadius: '9999px',
                backgroundColor: active ? quinaBlue : '#fff',
                color: active ? '#fff' : quinaBlue,
                border: `1px solid ${quinaBlue}`,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontWeight: 700,
                fontSize: 20,
            }}
        >
            {String(number).padStart(2, '0')}
        </div>
    );
}

export default function Play({ modality, prefilledNumbers = [] }) {
    const authUser = usePage().props.auth?.user;
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
    const [error, setError] = useState('');
    const [smartStrategy, setSmartStrategy] = useState('balanced');
    const [smartGamesCount, setSmartGamesCount] = useState(5);
    const [loadingSmartGenerate, setLoadingSmartGenerate] = useState(false);
    const [smartGames, setSmartGames] = useState([]);

    const quinaBlue = '#0c5a96';

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
            analyzeNumbers(prefilledNumbers, 'manual');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    async function handleGenerate() {
        setLoadingGenerate(true);
        setError('');

        try {
            const response = await axios.post(
                `/lottery/modalities/${modality.id}/generate`,
                { count }
            );

            const generatedNumbers = response.data.numbers || [];

            setNumbers(generatedNumbers);
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

        try {
            const response = await axios.post(
                `/lottery/modalities/${modality.id}/analyze`,
                { numbers: targetNumbers, source }
            );

            setAnalysis(response.data);
            setNumbers(targetNumbers);
        } catch (err) {
            setAnalysis(null);
            setError(err?.response?.data?.message || 'Erro ao analisar jogo.');
        } finally {
            setLoadingAnalyze(false);
        }
    }

    async function handleSmartGenerate() {
        setLoadingSmartGenerate(true);
        setError('');

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

            if (err?.response?.status === 401) {
                window.location.href = '/login';
                return;
            }

            setError(err?.response?.data?.message || 'Erro ao gerar jogos inteligentes.');
        } finally {
            setLoadingSmartGenerate(false);
        }
    }

    async function handleAnalyzeManual() {
        await analyzeNumbers(parsedManualNumbers, 'manual');
    }

    return (
        <div className="max-w-6xl mx-auto p-6 space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex flex-wrap gap-3">
                    <Link
                        href={`/lottery/modalities/${modality.id}/combination-history`}
                        className="px-4 py-2 rounded border"
                    >
                        Ver histórico
                    </Link>

                    {authUser ? (
                        <Link href="/lottery/my-bets" className="px-4 py-2 rounded border">
                            Minhas apostas
                        </Link>
                    ) : null}
                </div>

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

            {error ? (
                <div className="p-3 rounded border border-red-300 bg-red-50 text-red-700">
                    {error}
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
                        {!authUser ? (
                            <div className="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                Faça login para usar a análise inteligente e registrar apostas.
                            </div>
                        ) : null}

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
                            onClick={handleSmartGenerate}
                            disabled={loadingSmartGenerate}
                            className="px-4 py-2 rounded-xl border"
                        >
                            {loadingSmartGenerate ? 'Gerando inteligentes...' : 'Gerar jogos inteligentes'}
                        </button>

                        {smartGames.length > 0 ? (
                            <div className="space-y-3 pt-2">
                                {smartGames.map((game, index) => (
                                    <div key={index} className="rounded-xl border p-3 space-y-3">
                                        <div className="flex flex-wrap gap-2">
                                            {(game.numbers || []).map((number) => (
                                                <NumberBall key={`${index}-${number}`} number={number} />
                                            ))}
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => analyzeNumbers(game.numbers || [], 'generated')}
                                            className="px-4 py-2 rounded-xl border"
                                        >
                                            Usar essa combinação
                                        </button>
                                    </div>
                                ))}
                            </div>
                        ) : null}
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
                            onChange={(e) => setManualNumbers(e.target.value)}
                            className="w-full rounded-xl border px-3 py-2"
                            placeholder="Ex.: 04, 18, 22, 49, 71"
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

            {numbers.length > 0 ? (
                <section className="rounded-2xl border bg-white p-5 space-y-4">
                    <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                        Combinação atual
                    </h2>

                    <div className="flex flex-wrap gap-3">
                        {numbers.map((number) => (
                            <NumberBall key={number} number={number} />
                        ))}
                    </div>
                </section>
            ) : null}

            {analysis ? (
                <section className="rounded-2xl border bg-white p-5 space-y-4">
                    <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                        Resultado da análise
                    </h2>

                    <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div className="rounded-xl border p-3">Soma: <strong>{analysis.sum}</strong></div>
                        <div className="rounded-xl border p-3">Pares: <strong>{analysis.even_count}</strong></div>
                        <div className="rounded-xl border p-3">Ímpares: <strong>{analysis.odd_count}</strong></div>
                        <div className="rounded-xl border p-3">Frequência média: <strong>{analysis.average_frequency}</strong></div>
                    </div>

                    {analysis?.score ? (
                        <div className="rounded-xl border p-4 bg-slate-50">
                            Score: <strong>{analysis.score.value}/100</strong> — {analysis.score.label}
                        </div>
                    ) : null}

                    {analysis?.narrative?.headline ? (
                        <div className="rounded-xl border p-4">{analysis.narrative.headline}</div>
                    ) : null}
                </section>
            ) : null}
        </div>
    );
}

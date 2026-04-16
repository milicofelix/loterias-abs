import { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from '@inertiajs/react';
import NumberPicker from '@/Components/Lottery/NumberPicker';
import {
    ActionLink,
    HeroBanner,
    LotteryPage,
    NumberBall,
    SectionHeading,
    SurfaceCard,
    Tag,
    lotteryPalette,
} from './components/LotteryUi';

function InfoRow({ label, value }) {
    return (
        <div className="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3" style={{ borderColor: lotteryPalette.line }}>
            <span style={{ color: lotteryPalette.muted }}>{label}</span>
            <strong style={{ color: lotteryPalette.blueDark }}>{value}</strong>
        </div>
    );
}


function HistoricalPrizeSummary({ summary, drawCount }) {
    if (!summary) {
        return null;
    }

    const visibleHits = Object.entries(summary.hit_counts || {})
        .filter(([hit]) => Number(hit) >= 2 && Number(hit) <= Number(drawCount || 0));

    return (
        <div className="space-y-4">
            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <InfoRow label="Melhor histórico" value={`${summary.best_hit || 0} acertos`} />
                <InfoRow label="Já premiou" value={summary.ever_prized ? 'Sim' : 'Não'} />
                <InfoRow label="Ocorrências" value={summary.total_prized_occurrences ?? 0} />
                <InfoRow label="Concursos lidos" value={summary.contest_count_checked ?? 0} />
            </div>

            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {visibleHits.map(([hit, total]) => (
                    <InfoRow key={hit} label={`${hit} acertos`} value={total} />
                ))}
            </div>

            {summary.last_occurrence ? (
                <div
                    className="rounded-[22px] border px-4 py-4 text-sm leading-7 md:text-base"
                    style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff' }}
                >
                    Última ocorrência premiável no concurso <strong>{summary.last_occurrence.contest_number}</strong>
                    {summary.last_occurrence.draw_date ? ` (${summary.last_occurrence.draw_date})` : ''} com{' '}
                    <strong>{summary.last_occurrence.hits} acertos</strong>.
                </div>
            ) : (
                <div
                    className="rounded-[22px] border px-4 py-4 text-sm leading-7 md:text-base"
                    style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff' }}
                >
                    Essa combinação ainda não apareceu com 2 ou mais acertos no histórico disponível.
                </div>
            )}
        </div>
    );
}

export default function Play({ modality, prefilledNumbers = [] }) {
    const [count, setCount] = useState(prefilledNumbers.length > 0 ? prefilledNumbers.length : modality.bet_min_count);
    const [numbers, setNumbers] = useState(prefilledNumbers);
    const [manualNumbers, setManualNumbers] = useState(prefilledNumbers.length > 0 ? prefilledNumbers.join(', ') : '');
    const [analysis, setAnalysis] = useState(null);
    const [loadingGenerate, setLoadingGenerate] = useState(false);
    const [loadingAnalyze, setLoadingAnalyze] = useState(false);
    const [error, setError] = useState('');
    const [smartStrategy, setSmartStrategy] = useState('balanced');
    const [smartGamesCount, setSmartGamesCount] = useState(5);
    const [loadingSmartGenerate, setLoadingSmartGenerate] = useState(false);
    const [smartGames, setSmartGames] = useState([]);
    const [selectedNumbers, setSelectedNumbers] = useState([]);

    const maxNumbers = Math.min(
        modality?.bet_max_count || 5,
        Math.max(modality?.bet_min_count || 1, Number(count) || modality?.bet_min_count || 5)
    );
    const totalNumbers = modality?.total_numbers || 80;

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

        useEffect(() => {
        if (selectedNumbers.length > maxNumbers) {
            const trimmed = selectedNumbers.slice(0, maxNumbers);
            setSelectedNumbers(trimmed);
            setManualNumbers(trimmed.join(', '));
        }
    }, [maxNumbers, selectedNumbers, setManualNumbers]);

    async function handleGenerate() {
        setLoadingGenerate(true);
        setError('');

        try {
            const response = await axios.post(`/lottery/modalities/${modality.id}/generate`, { count });
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
            const response = await axios.post(`/lottery/modalities/${modality.id}/analyze`, {
                numbers: targetNumbers,
                source,
            });

            setAnalysis(response.data);
            setNumbers(targetNumbers);
        } catch (err) {
            setAnalysis(null);
            setError(err?.response?.data?.message || 'Erro ao analisar jogo.');
        } finally {
            setLoadingAnalyze(false);
        }
    }

    async function handleAnalyzeManual() {
        await analyzeNumbers(parsedManualNumbers, 'manual');
    }

    return (
        <LotteryPage>
            <div className="space-y-8">
                <HeroBanner
                    eyebrow="Área do jogador"
                    title="Gerar e analisar"
                    contest={modality.name}
                    subtitle="Monte combinações bonitas, rode leituras automáticas e compare palpites inteligentes em uma tela mais rica e premium."
                    art="single"
                >
                    <ActionLink href={`/lottery/modalities/${modality.id}/combination-history`}>Ver histórico</ActionLink>
                    <ActionLink href={`/lottery/modalities/${modality.id}`} variant="secondary">Voltar ao painel</ActionLink>
                </HeroBanner>

                {error ? (
                    <div className="rounded-[24px] border border-rose-200 bg-rose-50 px-5 py-4 font-semibold text-rose-700">
                        {error}
                    </div>
                ) : null}

                <div className="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                    <SurfaceCard>
                        <SectionHeading
                            eyebrow="Geração"
                            title="Criar nova combinação"
                            description={`Escolha entre geração simples e sugestões inteligentes. Números válidos de ${modality.min_number} a ${modality.max_number}.`}
                        />

                        <div className="grid gap-5 lg:grid-cols-2">
                            <div className="rounded-[24px] border p-5" style={{ borderColor: lotteryPalette.line, background: 'linear-gradient(180deg, #ffffff 0%, #f8fbff 100%)' }}>
                                <div className="text-lg font-bold" style={{ color: lotteryPalette.blueDark }}>Gerador rápido</div>
                                <label className="mt-4 block text-sm font-semibold" style={{ color: lotteryPalette.muted }}>
                                    Quantidade de números
                                </label>
                                <input
                                    type="number"
                                    min={modality.bet_min_count}
                                    max={modality.bet_max_count}
                                    value={count}
                                    onChange={(e) => setCount(Number(e.target.value))}
                                    className="mt-2 w-full rounded-2xl border bg-white px-4 py-3"
                                    style={{ borderColor: lotteryPalette.line }}
                                />
                                <button
                                    onClick={handleGenerate}
                                    disabled={loadingGenerate}
                                    className="mt-4 inline-flex w-full items-center justify-center rounded-2xl px-5 py-3 font-semibold text-white shadow-[0_16px_30px_rgba(12,90,150,0.18)]"
                                    style={{ background: 'linear-gradient(180deg, #1670b6 0%, #0c5a96 100%)', opacity: loadingGenerate ? 0.75 : 1 }}
                                >
                                    {loadingGenerate ? 'Gerando...' : 'Gerar jogo'}
                                </button>
                            </div>

                            <div className="rounded-[24px] border p-5" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff' }}>
                                <div className="text-lg font-bold" style={{ color: lotteryPalette.blueDark }}>Gerador inteligente</div>
                                <div className="mt-4 flex flex-wrap gap-2">
                                    {[
                                        { value: 'balanced', label: 'Equilibrado' },
                                        { value: 'hot', label: 'Quente' },
                                    ].map((item) => (
                                        <Tag key={item.value} active={smartStrategy === item.value} onClick={() => setSmartStrategy(item.value)}>
                                            {item.label}
                                        </Tag>
                                    ))}
                                </div>
                                <label className="mt-4 block text-sm font-semibold" style={{ color: lotteryPalette.muted }}>
                                    Quantidade de jogos
                                </label>
                                <input
                                    type="number"
                                    min={1}
                                    max={10}
                                    value={smartGamesCount}
                                    onChange={(e) => setSmartGamesCount(Number(e.target.value))}
                                    className="mt-2 w-full rounded-2xl border bg-white px-4 py-3"
                                    style={{ borderColor: lotteryPalette.line }}
                                />
                                <button
                                    onClick={async () => {
                                        setLoadingSmartGenerate(true);
                                        setError('');

                                        try {
                                            const response = await axios.post(`/lottery/modalities/${modality.id}/generate-smart`, {
                                                strategy: smartStrategy,
                                                games: smartGamesCount,
                                            });

                                            setSmartGames(response.data.games || []);
                                        } catch (err) {
                                            setSmartGames([]);
                                            setError(err?.response?.data?.message || 'Erro ao gerar jogos inteligentes.');
                                        } finally {
                                            setLoadingSmartGenerate(false);
                                        }
                                    }}
                                    disabled={loadingSmartGenerate}
                                    className="mt-4 inline-flex w-full items-center justify-center rounded-2xl border px-5 py-3 font-semibold"
                                    style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff', color: lotteryPalette.blue }}
                                >
                                    {loadingSmartGenerate ? 'Gerando inteligentes...' : 'Gerar jogos inteligentes'}
                                </button>
                            </div>
                        </div>
                    </SurfaceCard>

                    <SurfaceCard>
    <SectionHeading
        eyebrow="Entrada manual"
        title="Monte sua combinação"
        description={`Escolha ${maxNumbers} números clicando no painel abaixo.`}
    />

    <div
        className="rounded-[28px] border p-5 md:p-6"
        style={{
            borderColor: lotteryPalette.line,
            background: 'linear-gradient(180deg, #ffffff 0%, #f5f7fb 100%)',
        }}
    >
        <NumberPicker
            totalNumbers={totalNumbers}
            maxSelection={maxNumbers}
            selected={selectedNumbers}
            onChange={(nums) => {
                setSelectedNumbers(nums);
                setManualNumbers(nums.join(', '));
            }}
        />
    </div>

    <div className="mt-4 flex flex-wrap gap-3">
        <button
            type="button"
            onClick={() => {
                setSelectedNumbers([]);
                setManualNumbers('');
            }}
            className="inline-flex min-h-[46px] items-center justify-center rounded-2xl border px-4 font-semibold transition"
            style={{
                borderColor: lotteryPalette.line,
                backgroundColor: '#fff',
                color: lotteryPalette.blue,
            }}
        >
            Limpar seleção
        </button>

        <button
            onClick={handleAnalyzeManual}
            disabled={loadingAnalyze || selectedNumbers.length !== maxNumbers}
            className="inline-flex min-h-[46px] flex-1 items-center justify-center rounded-2xl px-5 py-3 font-semibold text-white"
            style={{
                background: selectedNumbers.length === maxNumbers
                    ? 'linear-gradient(180deg, #1670b6 0%, #0c5a96 100%)'
                    : '#9db8d1',
                opacity: loadingAnalyze ? 0.8 : 1,
            }}
        >
            {loadingAnalyze ? 'Analisando...' : 'Analisar combinação'}
        </button>
    </div>

    {selectedNumbers.length > 0 ? (
        <div className="mt-4 flex flex-wrap gap-2 text-sm" style={{ color: lotteryPalette.muted }}>
            {selectedNumbers.map((value) => (
                <span
                    key={value}
                    className="rounded-full border px-3 py-1 font-semibold"
                    style={{
                        borderColor: lotteryPalette.line,
                        backgroundColor: '#fff',
                    }}
                >
                    {String(value).padStart(2, '0')}
                </span>
            ))}
        </div>
    ) : null}
</SurfaceCard>
                </div>

                {smartGames.length > 0 ? (
                    <SurfaceCard>
                        <SectionHeading
                            eyebrow="Sugestões"
                            title="Jogos inteligentes sugeridos"
                            description={`Estratégia ativa: ${smartStrategy === 'hot' ? 'Quente' : 'Equilibrado'}. Use, compare e analise qualquer sugestão com um clique.`}
                        />

                        <div className="grid gap-4 xl:grid-cols-2">
                            {smartGames.map((game, index) => (
                                <div key={`${game.numbers.join('-')}-${index}`} className="rounded-[26px] border p-5" style={{ borderColor: lotteryPalette.line, background: 'linear-gradient(180deg, #ffffff 0%, #f8fbff 100%)' }}>
                                    <div className="flex flex-wrap gap-2">
                                        {game.numbers.map((number) => (
                                            <NumberBall key={number} number={number} size="sm" />
                                        ))}
                                    </div>

                                    <div className="mt-4 grid grid-cols-2 gap-3 text-sm">
                                        <InfoRow label="Score" value={game.weighted_score} />
                                        <InfoRow label="Perfil" value={game.profile} />
                                        <InfoRow label="Classificação" value={game.classification} />
                                        <InfoRow label="Frequentes" value={game.top_frequency_hits} />
                                    </div>

                                    <p className="mt-4 text-sm leading-7" style={{ color: lotteryPalette.muted }}>
                                        {game.reason}
                                    </p>

                                    <div className="mt-4 rounded-[22px] border p-4" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff' }}>
                                        <div className="text-sm font-bold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                            Histórico premiável
                                        </div>
                                        <div className="mt-3 grid grid-cols-2 gap-3 text-sm">
                                            <InfoRow label="Melhor" value={`${game.historical_prize_summary?.best_hit ?? 0} acertos`} />
                                            <InfoRow label="Ocorrências" value={game.historical_prize_summary?.total_prized_occurrences ?? 0} />
                                            <InfoRow label="2 acertos" value={game.historical_prize_summary?.hit_counts?.['2'] ?? 0} />
                                            <InfoRow label="3+ acertos" value={Object.entries(game.historical_prize_summary?.hit_counts || {}).filter(([hit]) => Number(hit) >= 3).reduce((sum, [, total]) => sum + Number(total || 0), 0)} />
                                        </div>
                                    </div>

                                    <div className="mt-4 flex flex-wrap gap-2">
                                        <button
                                            className="inline-flex min-h-[46px] items-center justify-center rounded-2xl border px-4 font-semibold"
                                            style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff', color: lotteryPalette.blue }}
                                            onClick={() => {
                                                setNumbers(game.numbers);
                                                setManualNumbers(game.numbers.join(', '));
                                            }}
                                        >
                                            Usar esta combinação
                                        </button>

                                        <button
                                            className="inline-flex min-h-[46px] items-center justify-center rounded-2xl px-4 font-semibold text-white"
                                            style={{ background: 'linear-gradient(180deg, #1670b6 0%, #0c5a96 100%)' }}
                                            onClick={() => analyzeNumbers(game.numbers, 'generated')}
                                        >
                                            Analisar agora
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </SurfaceCard>
                ) : null}

                <SurfaceCard>
                    <SectionHeading
                        eyebrow="Combinação atual"
                        title="Seu jogo em destaque"
                        description="Acompanhe a combinação selecionada antes de prosseguir para nova análise ou futuras apostas."
                    />

                    <div className="flex flex-wrap gap-3">
                        {numbers.length > 0 ? (
                            numbers.map((number) => <NumberBall key={number} number={number} size="lg" />)
                        ) : (
                            <span className="text-base" style={{ color: lotteryPalette.muted }}>
                                Nenhuma combinação selecionada ainda.
                            </span>
                        )}
                    </div>
                </SurfaceCard>

                {analysis ? (
                    <div className="grid gap-6 xl:grid-cols-2">
                        <SurfaceCard>
                            <SectionHeading eyebrow="Leitura base" title="Resumo da combinação" />
                            <div className="grid gap-3 sm:grid-cols-2">
                                <InfoRow label="Soma" value={analysis.sum} />
                                <InfoRow label="Amplitude" value={analysis.range} />
                                <InfoRow label="Pares" value={analysis.even_count} />
                                <InfoRow label="Ímpares" value={analysis.odd_count} />
                                <InfoRow label="Menor número" value={analysis.min} />
                                <InfoRow label="Maior número" value={analysis.max} />
                            </div>
                        </SurfaceCard>

                        <SurfaceCard>
                            <SectionHeading eyebrow="Indicadores" title="Comparação histórica" />
                            <div className="grid gap-3 sm:grid-cols-2">
                                <InfoRow label="Média de frequência" value={analysis.average_frequency} />
                                <InfoRow label="Média de atraso" value={analysis.average_delay} />
                                <InfoRow label="Mais frequentes" value={analysis.top_frequency_hits} />
                                <InfoRow label="Mais atrasados" value={analysis.top_delay_hits} />
                                <InfoRow label="Média histórica da soma" value={analysis.historical_averages?.sum} />
                                <InfoRow label="Diferença da soma" value={analysis.historical_comparison?.sum_diff} />
                            </div>
                        </SurfaceCard>

                        <SurfaceCard>
                            <SectionHeading eyebrow="Faixas" title="Distribuição numérica" />
                            <div className="space-y-3">
                                {Object.entries(analysis.range_distribution || {}).map(([label, total]) => (
                                    <InfoRow key={label} label={label} value={total} />
                                ))}
                            </div>
                        </SurfaceCard>

                        <SurfaceCard>
                            <SectionHeading eyebrow="Sequências" title="Consecutivos encontrados" />
                            {analysis.consecutive_count > 0 ? (
                                <div className="space-y-3">
                                    {analysis.consecutive_groups?.map((group, index) => (
                                        <div key={index} className="flex flex-wrap gap-2">
                                            {group.map((number) => (
                                                <NumberBall key={`${index}-${number}`} number={number} size="sm" subtle />
                                            ))}
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div style={{ color: lotteryPalette.muted }}>
                                    Nenhuma sequência consecutiva encontrada.
                                </div>
                            )}
                        </SurfaceCard>

                        <SurfaceCard className="xl:col-span-2">
                            <SectionHeading eyebrow="Narrativa" title="Leitura automática da combinação" />
                            <div className="rounded-[24px] border px-5 py-4" style={{ borderColor: lotteryPalette.line, backgroundColor: lotteryPalette.soft }}>
                                <div className="text-2xl font-black tracking-tight" style={{ color: lotteryPalette.blueDark }}>
                                    {analysis.narrative?.headline}
                                </div>
                            </div>

                            <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                {analysis.narrative?.insights?.map((item, index) => (
                                    <div
                                        key={index}
                                        className="rounded-[22px] border px-4 py-4 text-sm leading-7 md:text-base"
                                        style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff' }}
                                    >
                                        {item}
                                    </div>
                                ))}
                            </div>
                        </SurfaceCard>

                        <SurfaceCard className="xl:col-span-2">
                            <SectionHeading eyebrow="Histórico real" title="Premiação histórica da sequência" description="Veja se essa mesma combinação já alcançou 2, 3, 4 ou 5 acertos em algum concurso do histórico carregado." />
                            <HistoricalPrizeSummary summary={analysis.historical_prize_summary} drawCount={modality.draw_count} />
                        </SurfaceCard>

                        {analysis?.agent ? (
                            <SurfaceCard className="xl:col-span-2">
                                <SectionHeading eyebrow="Agente analista" title={analysis.agent.title || 'Leitura do agente'} />
                                <div className="rounded-[24px] border px-5 py-4 text-[15px] leading-7 md:text-base" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff' }}>
                                    {analysis.agent.summary}
                                </div>
                            </SurfaceCard>
                        ) : null}
                    </div>
                ) : null}
            </div>
        </LotteryPage>
    );
}

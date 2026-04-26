import { Link, router } from '@inertiajs/react';
import axios from 'axios';
import {
    ActionLink,
    EmptyState,
    HeroBanner,
    LotteryPage,
    NumberBall,
    SectionHeading,
    SurfaceCard,
    Tag,
    lotteryPalette,
} from './components/LotteryUi';

export default function CombinationHistory({ modality, items, filters = {} }) {
    const applySourceFilter = (source) => {
        router.get(
            `/lottery/modalities/${modality.id}/combination-history`,
            source ? { source } : {},
            { preserveState: true, preserveScroll: true }
        );
    };

    const handleDelete = (itemId) => {
        if (!window.confirm('Deseja excluir esta combinação do histórico?')) {
            return;
        }

        router.delete(`/lottery/modalities/${modality.id}/combination-history/${itemId}`, {
            preserveScroll: true,
        });
    };

    const handleClearAll = () => {
        if (!window.confirm('Deseja limpar todo o histórico de combinações?')) {
            return;
        }

        router.delete(`/lottery/modalities/${modality.id}/combination-history`, {
            preserveScroll: true,
        });
    };

    const tags = [
        { value: '', label: 'Todas' },
        { value: 'manual', label: 'Manuais' },
        { value: 'generated', label: 'Geradas' },
    ];

    return (
        <LotteryPage>
            <div className="space-y-8">
                <HeroBanner
                    eyebrow="Área do jogador"
                    title="Combinações salvas"
                    contest={modality.name}
                    subtitle="Reabra análises, compare ideias e transforme combinações promissoras em apostas com um visual mais rico e organizado."
                    art="single"
                    modality={modality}
                >
                    <ActionLink href={`/lottery/modalities/${modality.id}/play`}>Nova análise</ActionLink>
                    <ActionLink href={`/lottery/modalities/${modality.id}/bets`} variant="secondary">
                        Minhas apostas
                    </ActionLink>
                    <button
                        type="button"
                        onClick={handleClearAll}
                        className="inline-flex min-h-[52px] items-center justify-center rounded-2xl border border-rose-200 bg-white/90 px-5 py-3 text-sm font-semibold text-rose-700 md:px-6 md:text-base"
                    >
                        Limpar histórico
                    </button>
                </HeroBanner>

                <SurfaceCard>
                    <SectionHeading
                        eyebrow="Filtro"
                        title="Origem das combinações"
                        description="Separe palpites manuais dos jogos gerados automaticamente para navegar mais rápido."
                    />

                    <div className="flex flex-wrap gap-3">
                        {tags.map((option) => (
                            <Tag
                                key={option.label}
                                active={(filters.source || '') === option.value}
                                onClick={() => applySourceFilter(option.value)}
                            >
                                {option.label}
                            </Tag>
                        ))}
                    </div>
                </SurfaceCard>

                {items.data.length === 0 ? (
                    <EmptyState
                        title="Nenhuma combinação encontrada"
                        description="Assim que você analisar um jogo ou usar uma combinação inteligente, ela aparecerá aqui com score, leitura automática e atalhos para reaproveitar."
                    />
                ) : (
                    <div className="space-y-5">
                        {items.data.map((item) => (
                            <SurfaceCard key={item.id}>
                                <div className="flex flex-col gap-5">
                                    <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <div className="text-sm font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                                {item.source === 'manual' ? 'Combinação manual' : 'Jogo gerado'}
                                            </div>
                                            <div className="mt-2 text-2xl font-black tracking-tight" style={{ color: lotteryPalette.blueDark }}>
                                                {item.created_at}
                                            </div>
                                            {item.analysis_snapshot?.score ? (
                                                <div
                                                    className="mt-3 inline-flex rounded-full px-4 py-2 text-sm font-semibold"
                                                    style={{ backgroundColor: '#eef6ff', color: lotteryPalette.blue, border: `1px solid ${lotteryPalette.line}` }}
                                                >
                                                    Score {item.analysis_snapshot.score.value}/100 · {item.analysis_snapshot.score.label}
                                                </div>
                                            ) : null}
                                        </div>

                                       <div className="grid gap-2 sm:grid-cols-3">
                                            <button
                                                type="button"
                                                onClick={() => router.visit(`/lottery/modalities/${modality.id}/play?history_id=${item.id}`)}
                                                className="inline-flex min-h-[46px] items-center justify-center rounded-2xl border px-4 font-semibold"
                                                style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff', color: lotteryPalette.blue }}
                                            >
                                                Reanalisar
                                            </button>

                                            {item.bet_contest_number ? (
                                                <Link
                                                    href={`/lottery/modalities/${modality.id}/combination-history/${item.id}/check-bet`}
                                                    className="inline-flex min-h-[46px] items-center justify-center rounded-2xl border px-4 font-semibold"
                                                    style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff', color: lotteryPalette.blue }}
                                                >
                                                    Conferir aposta
                                                </Link>
                                            ) : (
                                                <button
                                                    type="button"
                                                    onClick={async () => {
                                                        try {
                                                            await axios.post(`/lottery/modalities/${modality.id}/combination-history/${item.id}/register-bet`);
                                                            router.reload({ preserveScroll: true });
                                                        } catch (error) {
                                                            alert(error?.response?.data?.message || 'Não foi possível registrar a aposta.');
                                                        }
                                                    }}
                                                    className="inline-flex min-h-[46px] items-center justify-center rounded-2xl px-4 font-semibold text-white"
                                                    style={{ background: 'linear-gradient(180deg, #1670b6 0%, #0c5a96 100%)' }}
                                                >
                                                    Apostar
                                                </button>
                                            )}

                                            <button
                                                type="button"
                                                onClick={() => handleDelete(item.id)}
                                                className="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 px-4 font-semibold text-rose-700"
                                            >
                                                Excluir
                                            </button>
                                        </div>
                                    </div>

                                    <div className="flex flex-wrap gap-3">
                                        {item.numbers.map((number) => (
                                            <NumberBall key={`${item.id}-${number}`} number={number} size="md" />
                                        ))}
                                    </div>

                                    {item.analysis_snapshot?.historical_prize_summary ? (
                                        <div className="grid gap-3 md:grid-cols-3">
                                            <div className="rounded-[22px] border px-4 py-3 text-sm" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff', color: '#344054' }}>
                                                Melhor histórico: <strong>{item.analysis_snapshot.historical_prize_summary.best_hit ?? 0} acertos</strong>
                                            </div>
                                            <div className="rounded-[22px] border px-4 py-3 text-sm" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff', color: '#344054' }}>
                                                Ocorrências premiáveis: <strong>{item.analysis_snapshot.historical_prize_summary.total_prized_occurrences ?? 0}</strong>
                                            </div>
                                            <div className="rounded-[22px] border px-4 py-3 text-sm" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff', color: '#344054' }}>
                                                Já premiou: <strong>{item.analysis_snapshot.historical_prize_summary.ever_prized ? 'Sim' : 'Não'}</strong>
                                            </div>
                                        </div>
                                    ) : null}

                                    {item.analysis_snapshot?.agent?.summary ? (
                                        <div
                                            className="rounded-[22px] border px-5 py-4 text-[15px] leading-7 md:text-base"
                                            style={{ borderColor: lotteryPalette.line, backgroundColor: lotteryPalette.soft, color: '#344054' }}
                                        >
                                            {item.analysis_snapshot.agent.summary}
                                        </div>
                                    ) : null}
                                </div>
                            </SurfaceCard>
                        ))}
                    </div>
                )}

                {items.links?.length > 0 ? (
                    <div className="flex gap-2 flex-wrap">
                        {items.links.map((link, i) => (
                            <button
                                key={i}
                                disabled={!link.url}
                                onClick={() => link.url && router.visit(link.url)}
                                className="min-h-[42px] rounded-xl px-4 text-sm font-semibold"
                                style={
                                    link.active
                                        ? { backgroundColor: lotteryPalette.blue, color: '#fff' }
                                        : { border: `1px solid ${lotteryPalette.line}`, backgroundColor: '#fff', color: '#344054' }
                                }
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                ) : null}
            </div>
        </LotteryPage>
    );
}

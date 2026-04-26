import { Link, router } from '@inertiajs/react';
import {
    ActionLink,
    EmptyState,
    HeroBanner,
    LotteryPage,
    MetricCard,
    NumberBall,
    SectionHeading,
    SurfaceCard,
    Tag,
    lotteryPalette,
} from './components/LotteryUi';

export default function MyBets({ items, filters = {}, dayOptions = [7, 15, 30, 60, 90] }) {
    const currentDays = Number(filters.days || dayOptions[0] || 7);

    return (
        <LotteryPage>
            <div className="space-y-8 md:space-y-10">
                <HeroBanner
                    eyebrow="Área do jogador"
                    title="Minhas apostas"
                    contest={`${currentDays} dias`}
                    subtitle="Acompanhe suas apostas recentes com mais clareza visual, melhor separação entre blocos e acesso rápido para conferência."
                    art="single"
                >
                    <ActionLink href="/lottery/modalities">Voltar para modalidades</ActionLink>
                    <ActionLink href="/lottery/my-bets" variant="secondary">
                        Atualizar listagem
                    </ActionLink>
                </HeroBanner>

                <div className="grid gap-4 md:grid-cols-3">
                    <MetricCard label="Apostas listadas" value={items?.data?.length ?? 0} />
                    <MetricCard label="Janela selecionada" value={`${currentDays} dias`} />
                    <MetricCard
                        label="Status"
                        value={items?.data?.length ? 'Com registros' : 'Sem registros'}
                    />
                </div>

                <SurfaceCard>
                    <SectionHeading
                        eyebrow="Filtro"
                        title="Período analisado"
                        description="Troque rapidamente a janela de tempo para revisar suas apostas mais recentes sem recarregar toda a experiência visual."
                    />

                    <div className="flex flex-wrap gap-3">
                        {dayOptions.map((days) => {
                            const active = currentDays === Number(days);

                            return (
                                <Tag
                                    key={days}
                                    active={active}
                                    onClick={() =>
                                        router.get('/lottery/my-bets', { days }, { preserveState: true, replace: true })
                                    }
                                >
                                    Últimos {days} dias
                                </Tag>
                            );
                        })}
                    </div>
                </SurfaceCard>

                {(items?.data ?? []).length === 0 ? (
                    <EmptyState
                        title="Nenhuma aposta encontrada"
                        description="Ainda não existem apostas no período selecionado. Assim que você registrar novos jogos, eles aparecerão aqui de forma organizada."
                    />
                ) : (
                    <div className="space-y-5">
                        {(items?.data ?? []).map((item) => (
                            <SurfaceCard key={item.id}>
                                <div className="flex flex-col gap-5">
                                    <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div className="space-y-3">
                                            <div className="text-sm font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                                Aposta registrada
                                            </div>

                                            <div>
                                                <div className="text-2xl font-black tracking-tight" style={{ color: lotteryPalette.blueDark }}>
                                                    {item.modality?.name}
                                                </div>
                                                <div className="mt-2 text-sm md:text-base" style={{ color: lotteryPalette.muted }}>
                                                    Concurso {item.bet_contest_number} · {item.bet_registered_at}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="flex flex-wrap gap-3">
                                            <div className="badge-soft">
                                                {item.numbers?.length ?? 0} números
                                            </div>

                                            {item.modality?.id ? (
                                                <Link
                                                    href={`/lottery/modalities/${item.modality.id}/combination-history/${item.id}/check-bet`}
                                                    className="btn-secondary"
                                                >
                                                    Conferir jogo
                                                </Link>
                                            ) : null}
                                        </div>
                                    </div>

                                    <div
                                        className="rounded-[24px] border p-4 md:p-5"
                                        style={{
                                            borderColor: lotteryPalette.line,
                                            background: 'linear-gradient(180deg, #ffffff 0%, #f8fbff 100%)',
                                        }}
                                    >
                                        <div className="flex flex-wrap gap-3">
                                            {item.numbers.map((number) => (
                                                <NumberBall key={number} number={number} size="sm" />
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </SurfaceCard>
                        ))}
                    </div>
                )}
            </div>
        </LotteryPage>
    );
}

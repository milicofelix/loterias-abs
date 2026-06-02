import { Link, router } from '@inertiajs/react';
import LotteryTopbar from '@/Components/LotteryTopbar';
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
} from '@/Components/LotteryUi';

const statusLabels = {
    pending: 'Pendente',
    prized: 'Premiada',
    not_prized: 'Não premiada',
};

const statusStyles = {
    pending: { backgroundColor: '#fff9e8', borderColor: '#f5d78e', color: '#8a6500' },
    prized: { backgroundColor: '#ecfdf3', borderColor: '#bbf7d0', color: '#047857' },
    not_prized: { backgroundColor: '#f8fafc', borderColor: '#e2e8f0', color: '#475569' },
};

export default function MyBets({
    modality = null,
    items,
    filters = {},
    dayOptions = [7, 15, 30, 60, 90],
    statusOptions = [],
    summary = {},
}) {
    const currentDays = Number(filters.days || dayOptions[0] || 7);
    const currentStatus = filters.status || '';
    const baseUrl = modality?.id ? `/lottery/modalities/${modality.id}/bets` : '/lottery/my-bets';

    const applyFilters = (changes = {}) => {
        router.get(
            baseUrl,
            {
                days: changes.days ?? currentDays,
                status: changes.status ?? currentStatus,
            },
            { preserveState: true, replace: true }
        );
    };

    return (
        <>
        <LotteryTopbar />

        <div className="max-w-7xl mx-auto p-6">
                <LotteryPage>
                    <div className="space-y-8 md:space-y-10">
	                        <HeroBanner
	                            eyebrow="Área do jogador"
	                            title="Minhas apostas"
	                            contest={modality?.name || `${currentDays} dias`}
	                            subtitle="Acompanhe suas apostas recentes com mais clareza visual, melhor separação entre blocos e acesso rápido para conferência."
	                            art="single"
	                        >
	                            <ActionLink href="/lottery/modalities">Voltar para modalidades</ActionLink>
	                            <ActionLink href={baseUrl} variant="secondary">
	                                Atualizar listagem
	                            </ActionLink>
                        </HeroBanner>

                        <div className="grid gap-4 md:grid-cols-3">
                            <MetricCard label="Apostas no período" value={summary.total ?? items?.data?.length ?? 0} />
                            <MetricCard label="Janela selecionada" value={`${currentDays} dias`} />
                            <MetricCard
                                label="Conferidas"
                                value={`${summary.checked ?? 0}/${summary.total ?? 0}`}
                            />
                        </div>

                        <div className="grid gap-4 md:grid-cols-3">
                            <MetricCard label="Pendentes" value={summary.pending ?? 0} />
                            <MetricCard label="Premiadas" value={summary.prized ?? 0} />
                            <MetricCard label="Não premiadas" value={summary.not_prized ?? 0} />
                        </div>

                        <SurfaceCard>
                            <SectionHeading
                                eyebrow="Filtro"
                                title="Período e status"
                                description="Troque rapidamente a janela de tempo para revisar suas apostas mais recentes sem recarregar toda a experiência visual."
                            />

                            <div className="flex flex-wrap gap-3">
                                {dayOptions.map((days) => {
                                    const active = currentDays === Number(days);

                                    return (
                                        <Tag
                                            key={days}
                                            active={active}
                                            onClick={() => applyFilters({ days })}
                                        >
                                            Últimos {days} dias
                                        </Tag>
                                    );
                                })}
                            </div>

                            <div className="mt-4 flex flex-wrap gap-3">
                                {statusOptions.map((option) => (
                                    <Tag
                                        key={option.value || 'all'}
                                        active={currentStatus === option.value}
                                        onClick={() => applyFilters({ status: option.value })}
                                    >
                                        {option.label}
                                    </Tag>
                                ))}
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
                                                            {item.bet_checked_at ? (
                                                                <div className="mt-1 text-sm" style={{ color: lotteryPalette.muted }}>
                                                                    Conferida em {item.bet_checked_at}
                                                                </div>
                                                            ) : null}
	                                                    </div>
	                                                </div>

	                                                <div className="flex flex-wrap gap-3">
	                                                    <div className="badge-soft">
	                                                        {item.numbers?.length ?? 0} números
	                                                    </div>

                                                        <div
                                                            className="inline-flex min-h-[36px] items-center rounded-full border px-4 text-sm font-semibold"
                                                            style={statusStyles[item.bet_status] || statusStyles.pending}
                                                        >
                                                            {statusLabels[item.bet_status] || 'Pendente'}
                                                        </div>

                                                    {item.modality?.id && item.bet_result_available ? (
	                                                        <Link
	                                                            href={`/lottery/modalities/${item.modality.id}/combination-history/${item.id}/check-bet`}
	                                                            className="btn-secondary"
	                                                        >
	                                                            {item.bet_checked_at ? 'Ver conferência' : 'Conferir jogo'}
	                                                        </Link>
                                                        ) : item.modality?.id ? (
                                                            <div
                                                                className="inline-flex min-h-[46px] items-center justify-center rounded-2xl border px-4 font-semibold"
                                                                style={{ borderColor: '#f5d78e', backgroundColor: '#fff9e8', color: '#8a6500' }}
                                                            >
                                                                Aguardando resultado
                                                            </div>
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

                                                {item.bet_result_snapshot?.check_result ? (
                                                    <div className="grid gap-3 md:grid-cols-3">
                                                        <div className="rounded-[22px] border px-4 py-3 text-sm" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff', color: '#344054' }}>
                                                            Acertos: <strong>{item.bet_result_snapshot.check_result.hit_count ?? 0}</strong>
                                                        </div>
                                                        <div className="rounded-[22px] border px-4 py-3 text-sm" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff', color: '#344054' }}>
                                                            Resultado: <strong>{item.bet_result_snapshot.check_result.is_prized ? 'Premiada' : 'Não premiada'}</strong>
                                                        </div>
                                                        <div className="rounded-[22px] border px-4 py-3 text-sm" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff', color: '#344054' }}>
                                                            Faixa: <strong>{item.bet_result_snapshot.check_result.prize_label || 'Não atingida'}</strong>
                                                        </div>
                                                    </div>
                                                ) : null}
	                                        </div>
	                                    </SurfaceCard>
	                                ))}
                            </div>
                        )}
                    </div>
            </LotteryPage>
        </div>
    </>
    );
}

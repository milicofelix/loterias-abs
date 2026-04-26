import {
    ActionLink,
    HeroBanner,
    LotteryPage,
    MetricCard,
    NumberBall,
    SectionHeading,
    SurfaceCard,
    lotteryPalette,
} from './components/LotteryUi';

export default function CheckBet({ modality, historyItem, officialResult, checkResult }) {
    const hits = checkResult?.hits || [];
    const prizeLabel = checkResult?.prize_label;
    const isPrized = Boolean(checkResult?.is_prized);
    const hitCount = Number(checkResult?.hit_count || 0);

    return (
        <LotteryPage>
            <div className="space-y-8 md:space-y-10">
                <HeroBanner
                    eyebrow="Conferência"
                    title="Conferir jogo"
                    contest={modality?.name}
                    subtitle="Compare seu jogo com o resultado oficial em uma visualização mais clara, com destaque de acertos e leitura rápida do desempenho."
                    art="single"
                    modality={modality}
                >
                    <ActionLink href={`/lottery/modalities/${modality.id}/combination-history`}>
                        Voltar ao histórico
                    </ActionLink>
                    <ActionLink href="/lottery/my-bets" variant="secondary">
                        Minhas apostas
                    </ActionLink>
                </HeroBanner>

                <div className="grid gap-4 md:grid-cols-3">
                    <MetricCard label="Concurso oficial" value={officialResult?.contest_number ?? '—'} />
                    <MetricCard label="Seu total de acertos" value={hitCount} />
                    <MetricCard label="Faixa premiável" value={isPrized ? prizeLabel : 'Não atingida'} />
                </div>

                <div className="grid gap-6 xl:grid-cols-2">
                    <SurfaceCard>
                        <SectionHeading
                            eyebrow="Resultado oficial"
                            title={`Concurso ${officialResult?.contest_number ?? '—'}`}
                            description="Números sorteados do concurso oficial da modalidade selecionada."
                        />

                        <div className="space-y-5">
                            <div className="badge-soft">{officialResult?.draw_date || 'Data não informada'}</div>

                            <div
                                className="rounded-[24px] border p-5 md:p-6"
                                style={{
                                    borderColor: lotteryPalette.line,
                                    background: 'linear-gradient(180deg, #ffffff 0%, #f8fbff 100%)',
                                }}
                            >
                                <div className="flex flex-wrap gap-3">
                                    {officialResult.numbers.map((number) => (
                                        <NumberBall key={number} number={number} size="lg" />
                                    ))}
                                </div>
                            </div>
                        </div>
                    </SurfaceCard>

                    <SurfaceCard>
                        <SectionHeading
                            eyebrow="Seu jogo"
                            title={`Concurso ${historyItem?.bet_contest_number ?? '—'}`}
                            description="Os números acertados aparecem destacados para facilitar a leitura visual da conferência."
                        />

                        <div className="space-y-5">
                            <div className="badge-soft">Registrado em {historyItem?.bet_registered_at || historyItem?.created_at || '—'}</div>

                            <div
                                className="rounded-[24px] border p-5 md:p-6"
                                style={{
                                    borderColor: lotteryPalette.line,
                                    background: 'linear-gradient(180deg, #ffffff 0%, #f8fbff 100%)',
                                }}
                            >
                                <div className="flex flex-wrap gap-3">
                                    {historyItem.numbers.map((number) => (
                                        <NumberBall key={number} number={number} size="lg" active={hits.includes(number)} subtle={!hits.includes(number)} />
                                    ))}
                                </div>
                            </div>

                            <div
                                className={`rounded-[22px] border px-5 py-4 text-sm leading-7 md:text-base ${
                                    isPrized
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : 'border-slate-200 bg-slate-50 text-slate-600'
                                }`}
                            >
                                <strong>
                                    {hitCount === 0
                                        ? 'Nenhum acerto.'
                                        : `${hitCount} ${hitCount === 1 ? 'acerto' : 'acertos'}.`}
                                </strong>{' '}
                                {isPrized
                                    ? `Faixa premiável identificada: ${prizeLabel}.`
                                    : 'Este jogo não atingiu faixa premiável nesse concurso.'}
                            </div>
                        </div>
                    </SurfaceCard>
                </div>
            </div>
        </LotteryPage>
    );
}

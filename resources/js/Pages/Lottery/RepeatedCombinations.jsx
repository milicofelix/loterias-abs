import {
    ActionLink,
    HeroBanner,
    LotteryPage,
    NumberBall,
    SectionHeading,
    SurfaceCard,
    MetricCard,
    lotteryPalette,
} from './components/LotteryUi';

export default function RepeatedCombinations({ modality, items = [], meta = null }) {
    return (
        <LotteryPage>
            <div className="space-y-8 md:space-y-10">
                <HeroBanner
                    eyebrow="Análise estatística"
                    title="Combinações repetidas"
                    contest={modality?.name}
                    subtitle="Veja quais sequências já apareceram mais de uma vez no histórico oficial carregado."
                    art="single"
                    modality={modality}
                >
                    <ActionLink href={`/lottery/modalities/${modality.id}`}>
                        Voltar ao painel
                    </ActionLink>
                    <ActionLink href={`/lottery/modalities/${modality.id}/history`} variant="secondary">
                        Ver histórico completo
                    </ActionLink>
                </HeroBanner>

                <div className="grid gap-4 md:grid-cols-3">
                    <MetricCard label="Concursos analisados" value={meta?.total_draws ?? 0} />
                    <MetricCard label="Combinações repetidas" value={meta?.repeated_count ?? 0} />
                    <MetricCard label="Padrões únicos" value={meta?.unique_patterns ?? 0} />
                </div>

                <SurfaceCard>
                    <SectionHeading
                        eyebrow="Resultado"
                        title="Repetições encontradas"
                        description="Listagem das combinações que apareceram em dois ou mais concursos."
                    />

                    {items.length === 0 ? (
                        <div
                            className="rounded-[24px] border px-5 py-8 text-base"
                            style={{
                                borderColor: lotteryPalette.line,
                                backgroundColor: '#fff',
                                color: lotteryPalette.muted,
                            }}
                        >
                            Nenhuma combinação repetida foi encontrada no histórico desta modalidade.
                        </div>
                    ) : (
                        <div className="space-y-5">
                            {items.map((item, index) => (
                                <div
                                    key={`${item.numbers.join('-')}-${index}`}
                                    className="rounded-[24px] border p-5"
                                    style={{
                                        borderColor: lotteryPalette.line,
                                        background: 'linear-gradient(180deg, #ffffff 0%, #f8fbff 100%)',
                                    }}
                                >
                                    <div className="flex flex-wrap items-center justify-between gap-4">
                                        <div className="flex flex-wrap gap-2">
                                            {item.numbers.map((number) => (
                                                <NumberBall key={number} number={number} size="sm" />
                                            ))}
                                        </div>

                                        <div
                                            className="rounded-full border px-4 py-2 text-sm font-bold"
                                            style={{
                                                borderColor: lotteryPalette.line,
                                                backgroundColor: '#fff',
                                                color: lotteryPalette.blueDark,
                                            }}
                                        >
                                            {item.occurrences} ocorrência(s)
                                        </div>
                                    </div>

                                    <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                        {item.draws.map((draw, drawIndex) => (
                                            <div
                                                key={`${draw.contest_number}-${drawIndex}`}
                                                className="rounded-[18px] border px-4 py-3 text-sm"
                                                style={{
                                                    borderColor: lotteryPalette.line,
                                                    backgroundColor: '#fff',
                                                    color: lotteryPalette.muted,
                                                }}
                                            >
                                                <div>
                                                    Concurso <strong style={{ color: lotteryPalette.blueDark }}>{draw.contest_number}</strong>
                                                </div>
                                                <div className="mt-1">
                                                    {draw.draw_date || 'Data não informada'}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </SurfaceCard>
            </div>
        </LotteryPage>
    );
}
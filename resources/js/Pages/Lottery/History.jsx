import { router } from '@inertiajs/react';
import { useState } from 'react';
import {
    ActionLink,
    HeroBanner,
    LotteryPage,
    NumberBall,
    SectionHeading,
    SurfaceCard,
    lotteryPalette,
} from './components/LotteryUi';

export default function History({ modality, draws, filters }) {
    const [search, setSearch] = useState(filters.q || '');

    function handleSearch(e) {
        e.preventDefault();

        router.get(`/lottery/modalities/${modality.id}/history`, { q: search }, { preserveState: true });
    }

    return (
        <LotteryPage>
            <div className="space-y-8">
                <HeroBanner
                    eyebrow="Histórico oficial"
                    title={modality.name}
                    contest="Linha do tempo dos concursos"
                    subtitle="Busque concursos específicos e navegue pelos sorteios anteriores com uma leitura mais limpa e visual."
                    art="single"
                    modality={modality}
                >
                    <ActionLink href={`/lottery/modalities/${modality.id}`}>Voltar ao painel</ActionLink>
                    <ActionLink href={`/lottery/modalities/${modality.id}/play`} variant="secondary">
                        Gerar e analisar jogo
                    </ActionLink>
                </HeroBanner>

                <SurfaceCard>
                    <SectionHeading
                        eyebrow="Busca"
                        title="Encontrar concurso"
                        description="Digite o número do concurso para ir direto ao resultado desejado."
                    />

                    <form onSubmit={handleSearch} className="grid gap-3 md:grid-cols-[1fr_auto]">
                        <input
                            type="text"
                            placeholder="Buscar por concurso (ex: 6988)"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="w-full rounded-2xl border bg-white px-4 py-3 text-base"
                            style={{ borderColor: lotteryPalette.line }}
                        />

                        <button
                            type="submit"
                            className="inline-flex min-h-[52px] items-center justify-center rounded-2xl px-6 text-base font-semibold text-white shadow-[0_16px_30px_rgba(12,90,150,0.18)]"
                            style={{ background: 'linear-gradient(180deg, #1670b6 0%, #0c5a96 100%)' }}
                        >
                            Buscar concurso
                        </button>
                    </form>
                </SurfaceCard>

                <div className="space-y-5">
                    {draws.data.map((draw) => (
                        <SurfaceCard key={draw.id} className="overflow-hidden">
                            <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div className="text-sm font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                        Resultado oficial
                                    </div>
                                    <div className="mt-2 text-3xl font-black tracking-tight" style={{ color: lotteryPalette.blueDark }}>
                                        Concurso {draw.contest_number}
                                    </div>
                                    <div className="mt-2 text-base" style={{ color: lotteryPalette.muted }}>
                                        {draw.draw_date}
                                    </div>
                                </div>

                                <div className="flex flex-wrap gap-3">
                                    {draw.numbers.map((n, idx) => (
                                        <NumberBall
                                            key={`${draw.id}-${idx}-${n.number ?? n}`}
                                            number={n.number ?? n}
                                            size="md"
                                        />
                                    ))}
                                </div>
                            </div>
                        </SurfaceCard>
                    ))}
                </div>

                <div className="flex gap-2 flex-wrap">
                    {draws.links.map((link, i) => (
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
            </div>
        </LotteryPage>
    );
}

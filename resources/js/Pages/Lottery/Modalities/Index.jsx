import { Link } from '@inertiajs/react';
import { getPlayInstruction } from '../components/modalityRules';
import LotteryLogo from '../components/LotteryLogo';

export default function Index({ modalities }) {
  const totalModalities = modalities?.length ?? 0;
  const biggestRange = modalities?.reduce((max, modality) => {
    const range = Number(modality.max_number) - Number(modality.min_number) + 1;
    return Math.max(max, range);
  }, 0);

  const biggestDraw = modalities?.reduce((max, modality) => {
    return Math.max(max, Number(modality.draw_count || 0));
  }, 0);

  return (
    <div className="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.10),_transparent_35%),linear-gradient(to_bottom,_#f8fbff,_#eef6ff_45%,_#f8fafc_100%)]">
      <div className="mx-auto max-w-7xl px-6 py-8 space-y-8">
        <section className="relative overflow-hidden rounded-[28px] border border-sky-200/60 bg-gradient-to-r from-slate-900 via-sky-900 to-blue-700 p-8 text-white shadow-[0_25px_80px_-30px_rgba(2,132,199,0.55)] md:p-10">
          <div className="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-cyan-300/20 blur-3xl" />
          <div className="absolute -bottom-20 left-1/3 h-56 w-56 rounded-full bg-blue-400/20 blur-3xl" />

          <div className="relative grid gap-8 lg:grid-cols-[1.5fr_0.9fr] lg:items-end">
            <div className="space-y-4">
                <LotteryLogo size="lg" />
              <div className="space-y-3">
                <h1 className="text-3xl font-extrabold tracking-tight md:text-5xl">
                  Modalidades disponíveis
                </h1>

                <p className="max-w-2xl text-sm leading-7 text-sky-50/90 md:text-base">
                  Explore cada modalidade com uma interface mais moderna, organizada e alinhada
                  ao restante do sistema. Acesse detalhes, instruções de jogo e análises com
                  muito mais clareza visual.
                </p>
              </div>
            </div>

            <div className="grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
              <div className="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md">
                <div className="text-xs font-semibold uppercase tracking-wide text-sky-100/80">
                  Modalidades
                </div>
                <div className="mt-2 text-2xl font-extrabold">{totalModalities}</div>
              </div>

              <div className="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md">
                <div className="text-xs font-semibold uppercase tracking-wide text-sky-100/80">
                  Maior faixa
                </div>
                <div className="mt-2 text-2xl font-extrabold">{biggestRange}</div>
              </div>

              <div className="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md">
                <div className="text-xs font-semibold uppercase tracking-wide text-sky-100/80">
                  Maior sorteio
                </div>
                <div className="mt-2 text-2xl font-extrabold">{biggestDraw}</div>
              </div>
            </div>
          </div>
        </section>

        <section className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          {modalities.map((modality) => {
            const range =
              Number(modality.max_number) - Number(modality.min_number) + 1;

            return (
              <Link
                key={modality.id}
                href={`/lottery/modalities/${modality.id}`}
                className="group relative overflow-hidden rounded-[26px] border border-sky-100 bg-white/90 p-6 shadow-[0_18px_50px_-30px_rgba(15,23,42,0.35)] backdrop-blur transition-all duration-300 hover:-translate-y-1.5 hover:border-sky-200 hover:shadow-[0_30px_70px_-30px_rgba(2,132,199,0.35)]"
              >
                <div className="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-sky-500 via-blue-600 to-cyan-400" />
                <div className="absolute right-0 top-0 h-28 w-28 translate-x-8 -translate-y-8 rounded-full bg-sky-100/70 blur-2xl transition duration-300 group-hover:bg-sky-200/80" />

                <div className="relative space-y-5">
                  <div className="flex items-start justify-between gap-4">
                    <div className="space-y-1">
                      <h2 className="text-2xl font-extrabold tracking-tight text-slate-800 transition group-hover:text-sky-700">
                        {modality.name}
                      </h2>
                      <p className="text-sm text-slate-500">
                        Modalidade pronta para consulta e geração
                      </p>
                    </div>

                    <div className="rounded-2xl border border-sky-200 bg-gradient-to-b from-sky-50 to-blue-50 px-4 py-3 text-center shadow-sm">
                      <div className="text-[11px] font-bold uppercase tracking-[0.18em] text-sky-700">
                        Sorteio
                      </div>
                      <div className="mt-1 text-lg font-extrabold text-sky-900">
                        {modality.draw_count}
                      </div>
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-3">
                    <div className="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                      <div className="text-[11px] font-bold uppercase tracking-wide text-slate-500">
                        Intervalo
                      </div>
                      <div className="mt-1 text-base font-bold text-slate-800">
                        {modality.min_number} até {modality.max_number}
                      </div>
                    </div>

                    <div className="rounded-2xl border border-sky-100 bg-sky-50/80 p-4">
                      <div className="text-[11px] font-bold uppercase tracking-wide text-sky-600">
                        Faixa total
                      </div>
                      <div className="mt-1 text-base font-bold text-sky-800">
                        {range} números
                      </div>
                    </div>
                  </div>

                  <div className="rounded-2xl border border-blue-100 bg-gradient-to-br from-slate-50 to-sky-50 p-4">
                    <div className="text-[11px] font-bold uppercase tracking-wide text-slate-500">
                      Como jogar
                    </div>
                    <p className="mt-2 text-sm leading-6 text-slate-600">
                      {getPlayInstruction(modality)}
                    </p>
                  </div>

                  <div className="flex items-center justify-between border-t border-slate-100 pt-1">
                    <span className="text-sm font-semibold text-sky-700">
                      Acessar modalidade
                    </span>

                    <span className="inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-700 transition duration-300 group-hover:bg-sky-600 group-hover:text-white">
                      →
                    </span>
                  </div>
                </div>
              </Link>
            );
          })}
        </section>
      </div>
    </div>
  );
}
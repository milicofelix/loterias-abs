import { Link, router, usePage } from '@inertiajs/react';
import { useMemo } from 'react';

function NumberBall({ number, active = true }) {
    return (
        <div
            style={{
                width: 46,
                height: 46,
                borderRadius: '9999px',
                backgroundColor: active ? '#0c5a96' : '#fff',
                border: active ? 'none' : '1px solid #d0d5dd',
                color: active ? '#fff' : '#344054',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontWeight: 700,
            }}
        >
            {String(number).padStart(2, '0')}
        </div>
    );
}

export default function Dashboard({
    modality,
    frequencies = {},
    delays = {},
    recentDraws = [],
    totalDraws = 0,
    latestContestNumber = null,
    dashboardNarrative = null,
    latestDrawExplanation = null,
    authRequiredActions = {},
}) {
    const { flash = {}, auth = {} } = usePage().props;

    const latestDraw = recentDraws[0] || null;

    const topFrequencies = useMemo(() => {
        return Object.entries(frequencies || {})
            .sort((a, b) => b[1] - a[1])
            .slice(0, 10);
    }, [frequencies]);

    const topDelays = useMemo(() => {
        return Object.entries(delays || {})
            .sort((a, b) => b[1] - a[1])
            .slice(0, 10);
    }, [delays]);

    const quinaBlue = '#0c5a96';
    const quinaBorder = '#d9e1ea';
    const quinaMuted = '#667085';
    const quinaBg = '#eef3f8';

    return (
        <div className="min-h-screen" style={{ backgroundColor: quinaBg }}>
            <div className="max-w-7xl mx-auto px-4 md:px-8 py-6 md:py-10 space-y-8">
                <header className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div style={{ color: quinaBlue, fontSize: 18, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.18em' }}>
                            Resultado oficial
                        </div>
                        <div className="flex flex-col gap-2 md:flex-row md:items-end md:gap-4 mt-2">
                            <h1 style={{ color: quinaBlue, fontSize: 'clamp(2.5rem, 5vw, 4rem)', fontWeight: 800, lineHeight: 1 }}>
                                {modality.name}
                            </h1>
                            <div style={{ color: quinaMuted, fontSize: 'clamp(1.2rem, 2vw, 2rem)', fontWeight: 500, lineHeight: 1.1 }}>
                                {latestContestNumber ? `Concurso ${latestContestNumber}` : ''}
                            </div>
                        </div>
                        <p className="mt-3" style={{ color: quinaMuted, fontSize: 18, fontWeight: 500 }}>
                            Resultados públicos. Para apostar, salvar combinações e usar análises inteligentes, entre na sua conta.
                        </p>
                    </div>

                    <div className="flex gap-3 flex-wrap">
                        {auth?.user ? (
                            <>
                                <Link href={`/lottery/modalities/${modality.id}/combination-history`} className="transition hover:opacity-95" style={{ backgroundColor: '#fff', color: quinaBlue, padding: '14px 22px', borderRadius: 16, fontWeight: 700, border: `1px solid ${quinaBorder}` }}>
                                    Minha área
                                </Link>
                                <Link href={`/lottery/modalities/${modality.id}/play`} className="transition hover:opacity-95" style={{ backgroundColor: quinaBlue, color: '#fff', padding: '14px 22px', borderRadius: 16, fontWeight: 700 }}>
                                    Gerar e analisar jogo
                                </Link>
                                <Link href="/logout" method="post" as="button" className="transition hover:opacity-95" style={{ backgroundColor: '#fff', color: quinaBlue, padding: '14px 22px', borderRadius: 16, fontWeight: 700, border: `1px solid ${quinaBorder}` }}>
                                    Sair
                                </Link>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className="transition hover:opacity-95" style={{ backgroundColor: '#fff', color: quinaBlue, padding: '14px 22px', borderRadius: 16, fontWeight: 700, border: `1px solid ${quinaBorder}` }}>
                                    Entrar
                                </Link>
                                <Link href="/register" className="transition hover:opacity-95" style={{ backgroundColor: '#fff', color: quinaBlue, padding: '14px 22px', borderRadius: 16, fontWeight: 700, border: `1px solid ${quinaBorder}` }}>
                                    Criar conta
                                </Link>
                                <Link href={authRequiredActions.play ? '/login' : `/lottery/modalities/${modality.id}/play`} className="transition hover:opacity-95" style={{ backgroundColor: quinaBlue, color: '#fff', padding: '14px 22px', borderRadius: 16, fontWeight: 700 }}>
                                    Entrar para apostar e analisar
                                </Link>
                            </>
                        )}
                    </div>
                </header>

                {flash.success ? <div className="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700 font-semibold">{flash.success}</div> : null}
                {flash.error ? <div className="rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 font-semibold">{flash.error}</div> : null}

                <section className="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
                    <div className="rounded-3xl border bg-white p-5 md:p-7" style={{ borderColor: quinaBorder }}>
                        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 className="text-2xl font-extrabold" style={{ color: quinaBlue }}>Último resultado</h2>
                                <p className="mt-1 text-slate-500">Concurso atual, números sorteados e visão pública do histórico.</p>
                            </div>
                            <Link href={`/lottery/modalities/${modality.id}/history`} className="rounded-2xl border bg-white px-4 py-3 font-semibold" style={{ borderColor: quinaBorder }}>
                                Ver histórico completo
                            </Link>
                        </div>

                        {latestDraw ? (
                            <div className="mt-6 rounded-3xl border p-5" style={{ borderColor: quinaBorder, backgroundColor: '#f8fbff' }}>
                                <div className="text-lg font-semibold text-slate-700">Concurso {latestDraw.contest_number}</div>
                                <div className="mt-1 text-sm text-slate-500">{latestDraw.draw_date || 'Data não informada'}</div>
                                <div className="mt-5 flex flex-wrap gap-3">
                                    {latestDraw.numbers.map((number) => <NumberBall key={number} number={number} />)}
                                </div>
                                {latestDrawExplanation ? (
                                    <div className="mt-5 rounded-2xl border px-4 py-3 text-slate-600" style={{ borderColor: quinaBorder }}>
                                        {typeof latestDrawExplanation === 'string' ? latestDrawExplanation : latestDrawExplanation.summary || latestDrawExplanation.headline || 'Explicação do último sorteio disponível.'}
                                    </div>
                                ) : null}
                            </div>
                        ) : (
                            <div className="mt-6 rounded-3xl border p-5 text-slate-500" style={{ borderColor: quinaBorder }}>
                                Ainda não há sorteios cadastrados para esta modalidade.
                            </div>
                        )}
                    </div>

                    <div className="rounded-3xl border bg-white p-5 md:p-7" style={{ borderColor: quinaBorder }}>
                        <h2 className="text-2xl font-extrabold" style={{ color: quinaBlue }}>Resumo rápido</h2>
                        <div className="mt-5 grid gap-4">
                            <div className="rounded-2xl border p-4" style={{ borderColor: quinaBorder }}>
                                <div className="text-sm text-slate-500">Total de concursos</div>
                                <div className="mt-1 text-3xl font-extrabold text-slate-800">{totalDraws}</div>
                            </div>
                            <div className="rounded-2xl border p-4" style={{ borderColor: quinaBorder }}>
                                <div className="text-sm text-slate-500">Concurso mais recente</div>
                                <div className="mt-1 text-3xl font-extrabold text-slate-800">{latestContestNumber || '—'}</div>
                            </div>
                            {dashboardNarrative ? (
                                <div className="rounded-2xl border p-4 text-slate-600" style={{ borderColor: quinaBorder }}>
                                    {typeof dashboardNarrative === 'string' ? dashboardNarrative : dashboardNarrative.summary || dashboardNarrative.headline || 'Resumo inteligente disponível.'}
                                </div>
                            ) : null}
                        </div>
                    </div>
                </section>

                <section className="grid gap-6 lg:grid-cols-2">
                    <div className="rounded-3xl border bg-white p-5 md:p-7" style={{ borderColor: quinaBorder }}>
                        <h2 className="text-2xl font-extrabold" style={{ color: quinaBlue }}>Números mais frequentes</h2>
                        <div className="mt-5 space-y-3">
                            {topFrequencies.length === 0 ? (
                                <div className="text-slate-500">Sem dados suficientes.</div>
                            ) : topFrequencies.map(([number, total]) => (
                                <div key={number} className="flex items-center justify-between rounded-2xl border px-4 py-3" style={{ borderColor: quinaBorder }}>
                                    <div className="flex items-center gap-3">
                                        <NumberBall number={number} />
                                        <span className="font-semibold text-slate-700">Número {String(number).padStart(2, '0')}</span>
                                    </div>
                                    <span className="font-bold text-slate-800">{total}x</span>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="rounded-3xl border bg-white p-5 md:p-7" style={{ borderColor: quinaBorder }}>
                        <h2 className="text-2xl font-extrabold" style={{ color: quinaBlue }}>Números mais atrasados</h2>
                        <div className="mt-5 space-y-3">
                            {topDelays.length === 0 ? (
                                <div className="text-slate-500">Sem dados suficientes.</div>
                            ) : topDelays.map(([number, total]) => (
                                <div key={number} className="flex items-center justify-between rounded-2xl border px-4 py-3" style={{ borderColor: quinaBorder }}>
                                    <div className="flex items-center gap-3">
                                        <NumberBall number={number} active={false} />
                                        <span className="font-semibold text-slate-700">Número {String(number).padStart(2, '0')}</span>
                                    </div>
                                    <span className="font-bold text-slate-800">{total} concursos</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="rounded-3xl border bg-white p-5 md:p-7" style={{ borderColor: quinaBorder }}>
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 className="text-2xl font-extrabold" style={{ color: quinaBlue }}>Últimos concursos</h2>
                            <p className="mt-1 text-slate-500">A parte pública do sistema continua focada nos resultados oficiais.</p>
                        </div>
                        {auth?.user ? (
                            <Link href={`/lottery/modalities/${modality.id}/bets`} className="rounded-2xl px-4 py-3 font-semibold text-white" style={{ backgroundColor: quinaBlue }}>
                                Minhas apostas
                            </Link>
                        ) : null}
                    </div>
                    <div className="mt-6 space-y-4">
                        {recentDraws.map((draw) => (
                            <div key={draw.id} className="rounded-2xl border p-4" style={{ borderColor: quinaBorder }}>
                                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <div className="font-semibold text-slate-800">Concurso {draw.contest_number}</div>
                                        <div className="text-sm text-slate-500">{draw.draw_date || 'Data não informada'}</div>
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        {draw.numbers.map((number) => <NumberBall key={`${draw.id}-${number}`} number={number} />)}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>
            </div>
        </div>
    );
}

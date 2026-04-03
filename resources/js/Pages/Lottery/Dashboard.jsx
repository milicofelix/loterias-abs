import { Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import {
    BarChart,
    Bar,
    CartesianGrid,
    XAxis,
    YAxis,
    Tooltip,
    ResponsiveContainer,
} from 'recharts';

export default function Dashboard({
    modality,
    frequencies,
    delays,
    frequenciesLast10,
    frequenciesLast20,
    frequenciesLast50,
    delaysLast10,
    delaysLast20,
    delaysLast50,
    recentDraws = [],
    totalDraws = 0,
    latestContestNumber = null,
    dashboardNarrative = null,
    latestDrawExplanation = null,
}) {
    const [selectedWindow, setSelectedWindow] = useState('all');

    const selectedFrequencies = useMemo(() => {
        switch (selectedWindow) {
            case '10':
                return frequenciesLast10;
            case '20':
                return frequenciesLast20;
            case '50':
                return frequenciesLast50;
            default:
                return frequencies;
        }
    }, [selectedWindow, frequencies, frequenciesLast10, frequenciesLast20, frequenciesLast50]);

    const selectedDelays = useMemo(() => {
        switch (selectedWindow) {
            case '10':
                return delaysLast10;
            case '20':
                return delaysLast20;
            case '50':
                return delaysLast50;
            default:
                return delays;
        }
    }, [selectedWindow, delays, delaysLast10, delaysLast20, delaysLast50]);

    const topFrequencies = Object.entries(selectedFrequencies)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 10);

    const topDelays = Object.entries(selectedDelays)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 10);

    const chartData = Object.entries(selectedFrequencies).map(([number, total]) => ({
        number: String(number).padStart(2, '0'),
        total,
    }));

    const latestDraw = recentDraws.length > 0 ? recentDraws[0] : null;

    const quinaBlue = '#0c5a96';
    const quinaBlueDark = '#0b3e6a';
    const quinaBall = '#0f4c81';
    const quinaBg = '#eef3f8';
    const quinaCard = '#ffffff';
    const quinaBorder = '#d9e1ea';
    const quinaMuted = '#667085';

    return (
        <div className="min-h-screen" style={{ backgroundColor: quinaBg }}>
            <div className="max-w-7xl mx-auto px-4 md:px-8 py-6 md:py-10 space-y-8">
                <header className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div
                            style={{
                                color: quinaBlue,
                                fontSize: 18,
                                fontWeight: 700,
                                textTransform: 'uppercase',
                                letterSpacing: '0.18em',
                            }}
                        >
                            Resultado
                        </div>

                        <div className="flex flex-col gap-2 md:flex-row md:items-end md:gap-4 mt-2">
                            <h1
                                style={{
                                    color: quinaBlue,
                                    fontSize: 'clamp(2.5rem, 5vw, 4rem)',
                                    fontWeight: 800,
                                    lineHeight: 1,
                                }}
                            >
                                {modality.name}
                            </h1>

                            <div
                                style={{
                                    color: quinaMuted,
                                    fontSize: 'clamp(1.2rem, 2vw, 2rem)',
                                    fontWeight: 500,
                                    lineHeight: 1.1,
                                }}
                            >
                                {latestContestNumber ? `Concurso ${latestContestNumber}` : ''}
                            </div>
                        </div>

                        <p
                            className="mt-3"
                            style={{
                                color: quinaMuted,
                                fontSize: 20,
                                fontWeight: 500,
                            }}
                        >
                            Modalidade: {modality.min_number} a {modality.max_number} • Sorteio de {modality.draw_count} números
                        </p>
                    </div>

                    <div className="flex gap-3 flex-wrap">
                        <Link
                            href={`/lottery/modalities/${modality.id}/play`}
                            className="transition hover:opacity-95"
                            style={{
                                backgroundColor: quinaBlue,
                                color: '#fff',
                                padding: '14px 22px',
                                borderRadius: 16,
                                fontWeight: 700,
                                boxShadow: '0 8px 24px rgba(12, 90, 150, 0.18)',
                            }}
                        >
                            Gerar e analisar jogo
                        </Link>
                    </div>
                </header>

                {latestDraw ? (
                    <div className="grid xl:grid-cols-[1.7fr_1fr] gap-6">
                        <section
                            style={{
                                backgroundColor: quinaCard,
                                border: `1px solid ${quinaBorder}`,
                                borderRadius: 28,
                                boxShadow: '0 8px 32px rgba(15, 76, 129, 0.06)',
                                padding: 32,
                            }}
                        >
                            <div>
                                <div
                                    style={{
                                        color: quinaMuted,
                                        fontSize: 16,
                                        fontWeight: 600,
                                        textTransform: 'uppercase',
                                        letterSpacing: '0.08em',
                                    }}
                                >
                                    Último resultado
                                </div>

                                <h2
                                    className="mt-2"
                                    style={{
                                        color: quinaBlue,
                                        fontSize: 'clamp(2rem, 3vw, 3rem)',
                                        fontWeight: 800,
                                        lineHeight: 1.05,
                                    }}
                                >
                                    Concurso {latestDraw.contest_number}
                                </h2>

                                <p
                                    className="mt-2"
                                    style={{
                                        color: quinaMuted,
                                        fontSize: 22,
                                        fontWeight: 500,
                                    }}
                                >
                                    {latestDraw.draw_date}
                                </p>
                            </div>

                            <div className="mt-10">
                                <div
                                    style={{
                                        color: quinaMuted,
                                        fontSize: 18,
                                        fontWeight: 600,
                                        marginBottom: 18,
                                    }}
                                >
                                    Números sorteados
                                </div>

                                <div className="flex flex-wrap gap-4">
                                    {latestDraw.numbers.map((number) => (
                                        <div
                                            key={number}
                                            style={{
                                                width: 84,
                                                height: 84,
                                                minWidth: 84,
                                                minHeight: 84,
                                                borderRadius: '9999px',
                                                backgroundColor: quinaBall,
                                                color: '#fff',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                                fontSize: 34,
                                                fontWeight: 700,
                                                lineHeight: 1,
                                                boxShadow: '0 10px 24px rgba(15, 76, 129, 0.22)',
                                            }}
                                        >
                                            {String(number).padStart(2, '0')}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="mt-10 grid md:grid-cols-3 gap-4">
                                <InfoCard
                                    label="Total de concursos"
                                    value={totalDraws}
                                    color={quinaBlue}
                                />
                                <InfoCard
                                    label="Último concurso"
                                    value={latestContestNumber ?? '—'}
                                    color={quinaBlue}
                                />
                                <InfoCard
                                    label="Faixa válida"
                                    value={`${modality.min_number} - ${modality.max_number}`}
                                    color={quinaBlue}
                                />
                            </div>
                        </section>

                        <aside
                            style={{
                                backgroundColor: quinaCard,
                                border: `1px solid ${quinaBorder}`,
                                borderRadius: 28,
                                boxShadow: '0 8px 32px rgba(15, 76, 129, 0.06)',
                                padding: 32,
                            }}
                        >
                            <h3
                                style={{
                                    color: quinaBlue,
                                    fontSize: 30,
                                    fontWeight: 800,
                                    marginBottom: 24,
                                }}
                            >
                                Premiação e detalhes
                            </h3>

                            <div className="space-y-5">
                                <DetailLine
                                    label="Observação"
                                    value={
                                        latestDraw.metadata?.observação ||
                                        latestDraw.metadata?.observacao ||
                                        '—'
                                    }
                                />

                                <DetailLine
                                    label="Ganhadores 5 acertos"
                                    value={latestDraw.metadata?.['Ganhadores 5 acertos'] ?? '—'}
                                />

                                <DetailLine
                                    label="Rateio 5 acertos"
                                    value={latestDraw.metadata?.['Rateio 5 acertos'] ?? '—'}
                                />

                                <DetailLine
                                    label="Ganhadores 4 acertos"
                                    value={latestDraw.metadata?.['Ganhadores 4 acertos'] ?? '—'}
                                />

                                <DetailLine
                                    label="Rateio 4 acertos"
                                    value={latestDraw.metadata?.['Rateio 4 acertos'] ?? '—'}
                                />

                                <DetailLine
                                    label="Estimativa prêmio"
                                    value={latestDraw.metadata?.['Estimativa Premio'] ?? '—'}
                                />
                            </div>
                        </aside>
                    </div>
                ) : null}

                {latestDrawExplanation ? (
                    <section
                        style={{
                            backgroundColor: quinaCard,
                            border: `1px solid ${quinaBorder}`,
                            borderRadius: 28,
                            boxShadow: '0 8px 32px rgba(15, 76, 129, 0.05)',
                            padding: 28,
                        }}
                    >
                        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2
                                    style={{
                                        color: quinaBlue,
                                        fontSize: 30,
                                        fontWeight: 800,
                                    }}
                                >
                                    Agente explicador do concurso
                                </h2>

                                <p
                                    className="mt-1"
                                    style={{
                                        color: quinaMuted,
                                        fontSize: 18,
                                        fontWeight: 500,
                                    }}
                                >
                                    {latestDrawExplanation.name} · v{latestDrawExplanation.version}
                                </p>
                            </div>

                            <div
                                style={{
                                    color: quinaBlueDark,
                                    fontSize: 18,
                                    fontWeight: 700,
                                }}
                            >
                                Concurso {latestDrawExplanation.contest_number}
                            </div>
                        </div>

                        <div
                            className="mt-5"
                            style={{
                                backgroundColor: '#f8fbff',
                                border: '1px solid #d6e8fb',
                                borderRadius: 16,
                                padding: 16,
                            }}
                        >
                            <div style={{ color: quinaBlue, fontSize: 18, fontWeight: 700 }}>
                                {latestDrawExplanation.summary}
                            </div>
                        </div>

                        {latestDrawExplanation.highlights?.length ? (
                            <div className="mt-5 grid md:grid-cols-2 gap-3">
                                {latestDrawExplanation.highlights.map((item, index) => (
                                    <div
                                        key={index}
                                        className="rounded-xl border px-4 py-3 bg-slate-50 text-slate-700"
                                    >
                                        {item}
                                    </div>
                                ))}
                            </div>
                        ) : null}

                        <div className="mt-5 grid md:grid-cols-3 gap-4">
                            <InfoCard
                                label="Soma"
                                value={latestDrawExplanation.statistics?.sum ?? '—'}
                                color={quinaBlue}
                            />
                            <InfoCard
                                label="Amplitude"
                                value={latestDrawExplanation.statistics?.range ?? '—'}
                                color={quinaBlue}
                            />
                            <InfoCard
                                label="Pares / Ímpares"
                                value={`${latestDrawExplanation.statistics?.even_count ?? '—'} / ${latestDrawExplanation.statistics?.odd_count ?? '—'}`}
                                color={quinaBlue}
                            />
                        </div>
                    </section>
                ) : null}

                {dashboardNarrative ? (
                    <section
                        className="rounded-2xl border bg-white p-5 space-y-4"
                        style={{
                            borderColor: quinaBorder,
                            boxShadow: '0 8px 32px rgba(15, 76, 129, 0.05)',
                        }}
                    >
                        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 style={{ color: quinaBlue, fontSize: 24, fontWeight: 700 }}>
                                    Agente narrador do dashboard
                                </h2>

                                <div className="text-sm text-slate-500 mt-1">
                                    {dashboardNarrative.name} · v{dashboardNarrative.version}
                                </div>
                            </div>
                        </div>

                        <div
                            style={{
                                backgroundColor: '#f8fbff',
                                border: '1px solid #d6e8fb',
                                borderRadius: 16,
                                padding: 16,
                            }}
                        >
                            <div style={{ color: quinaBlue, fontSize: 18, fontWeight: 700 }}>
                                {dashboardNarrative.headline}
                            </div>
                        </div>

                        {dashboardNarrative.highlights?.length ? (
                            <div className="space-y-2">
                                {dashboardNarrative.highlights.map((item, index) => (
                                    <div
                                        key={index}
                                        className="rounded-xl border px-4 py-3 bg-slate-50 text-slate-700"
                                    >
                                        {item}
                                    </div>
                                ))}
                            </div>
                        ) : null}

                        <div className="rounded-xl border px-4 py-4 text-slate-700">
                            {dashboardNarrative.summary}
                        </div>
                    </section>
                ) : null}

                <section
                    style={{
                        backgroundColor: quinaCard,
                        border: `1px solid ${quinaBorder}`,
                        borderRadius: 28,
                        boxShadow: '0 8px 32px rgba(15, 76, 129, 0.05)',
                        padding: 28,
                    }}
                >
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2
                                style={{
                                    color: quinaBlue,
                                    fontSize: 30,
                                    fontWeight: 800,
                                }}
                            >
                                Janela de análise
                            </h2>
                            <p
                                className="mt-1"
                                style={{
                                    color: quinaMuted,
                                    fontSize: 18,
                                    fontWeight: 500,
                                }}
                            >
                                Compare estatísticas da base inteira com os concursos mais recentes.
                            </p>
                        </div>

                        <div className="flex gap-2 flex-wrap">
                            {[
                                { value: 'all', label: 'Geral' },
                                { value: '10', label: 'Últimos 10' },
                                { value: '20', label: 'Últimos 20' },
                                { value: '50', label: 'Últimos 50' },
                            ].map((option) => {
                                const active = selectedWindow === option.value;

                                return (
                                    <button
                                        key={option.value}
                                        type="button"
                                        onClick={() => setSelectedWindow(option.value)}
                                        style={{
                                            backgroundColor: active ? quinaBlue : '#fff',
                                            color: active ? '#fff' : quinaBlueDark,
                                            border: `1px solid ${active ? quinaBlue : quinaBorder}`,
                                            padding: '12px 18px',
                                            borderRadius: 14,
                                            fontWeight: 700,
                                            fontSize: 16,
                                        }}
                                    >
                                        {option.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </section>

                <section
                    style={{
                        backgroundColor: quinaCard,
                        border: `1px solid ${quinaBorder}`,
                        borderRadius: 28,
                        boxShadow: '0 8px 32px rgba(15, 76, 129, 0.05)',
                        padding: 28,
                    }}
                >
                    <div className="mb-5">
                        <h2
                            style={{
                                color: quinaBlue,
                                fontSize: 30,
                                fontWeight: 800,
                            }}
                        >
                            Frequência por número
                        </h2>
                        <p
                            className="mt-1"
                            style={{
                                color: quinaMuted,
                                fontSize: 18,
                                fontWeight: 500,
                            }}
                        >
                            {selectedWindow === 'all'
                                ? 'Distribuição de frequência considerando toda a base importada.'
                                : `Distribuição de frequência considerando os últimos ${selectedWindow} concursos.`}
                        </p>
                    </div>

                    <div className="h-[380px] md:h-[460px]">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart data={chartData}>
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis
                                    dataKey="number"
                                    interval={0}
                                    angle={-45}
                                    textAnchor="end"
                                    height={70}
                                />
                                <YAxis allowDecimals={false} />
                                <Tooltip />
                                <Bar dataKey="total" fill={quinaBlue} radius={[8, 8, 0, 0]} />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </section>

                <div className="grid xl:grid-cols-2 gap-6">
                    <StatsPanel
                        title={`Mais frequentes ${selectedWindow === 'all' ? '(geral)' : `(últimos ${selectedWindow})`}`}
                        items={topFrequencies}
                        color={quinaBlue}
                    />

                    <StatsPanel
                        title={`Mais atrasados ${selectedWindow === 'all' ? '(geral)' : `(últimos ${selectedWindow})`}`}
                        items={topDelays}
                        color={quinaBlue}
                    />
                </div>

                <section
                    style={{
                        backgroundColor: quinaCard,
                        border: `1px solid ${quinaBorder}`,
                        borderRadius: 28,
                        boxShadow: '0 8px 32px rgba(15, 76, 129, 0.05)',
                        padding: 28,
                    }}
                >
                    <div className="mb-6">
                        <h2
                            style={{
                                color: quinaBlue,
                                fontSize: 30,
                                fontWeight: 800,
                            }}
                        >
                            Últimos concursos importados
                        </h2>
                        <p
                            className="mt-1"
                            style={{
                                color: quinaMuted,
                                fontSize: 18,
                                fontWeight: 500,
                            }}
                        >
                            Visualização dos resultados mais recentes já carregados no sistema.
                        </p>
                    </div>

                    {recentDraws.length === 0 ? (
                        <p style={{ color: quinaMuted, fontSize: 16 }}>
                            Nenhum concurso importado ainda.
                        </p>
                    ) : (
                        <div
                            style={{
                                border: `1px solid ${quinaBorder}`,
                                borderRadius: 22,
                                overflow: 'hidden',
                            }}
                        >
                            {recentDraws.map((draw, index) => (
                                <div
                                    key={draw.id}
                                    style={{
                                        padding: 20,
                                        backgroundColor: '#fff',
                                        borderBottom:
                                            index === recentDraws.length - 1
                                                ? 'none'
                                                : `1px solid ${quinaBorder}`,
                                    }}
                                >
                                    <div className="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                                        <div>
                                            <div
                                                style={{
                                                    color: quinaBlue,
                                                    fontWeight: 700,
                                                    fontSize: 22,
                                                }}
                                            >
                                                Concurso {draw.contest_number}
                                            </div>

                                            <div
                                                style={{
                                                    color: '#111827',
                                                    fontSize: 18,
                                                    marginTop: 4,
                                                }}
                                            >
                                                {draw.draw_date}
                                            </div>
                                        </div>

                                        <div className="flex flex-wrap gap-2">
                                            {draw.numbers.map((number) => (
                                                <div
                                                    key={number}
                                                    style={{
                                                        width: 46,
                                                        height: 46,
                                                        minWidth: 46,
                                                        minHeight: 46,
                                                        borderRadius: '9999px',
                                                        backgroundColor: quinaBall,
                                                        color: '#fff',
                                                        display: 'flex',
                                                        alignItems: 'center',
                                                        justifyContent: 'center',
                                                        fontWeight: 700,
                                                        fontSize: 20,
                                                        lineHeight: 1,
                                                        boxShadow: '0 2px 6px rgba(0,0,0,0.10)',
                                                    }}
                                                >
                                                    {String(number).padStart(2, '0')}
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    <div
                                        className="mt-4"
                                        style={{
                                            color: '#111827',
                                            fontSize: 16,
                                        }}
                                    >
                                        <strong>Observação:</strong>{' '}
                                        {draw.metadata?.observação || draw.metadata?.observacao || '—'}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </div>
    );
}

function InfoCard({ label, value, color }) {
    return (
        <div
            style={{
                border: '1px solid #d9e1ea',
                borderRadius: 22,
                backgroundColor: '#f8fbff',
                padding: 20,
            }}
        >
            <div
                style={{
                    color: '#667085',
                    fontSize: 15,
                    fontWeight: 600,
                }}
            >
                {label}
            </div>

            <div
                className="mt-2"
                style={{
                    color,
                    fontSize: 40,
                    fontWeight: 800,
                    lineHeight: 1,
                }}
            >
                {value}
            </div>
        </div>
    );
}

function DetailLine({ label, value }) {
    return (
        <div
            style={{
                borderBottom: '1px solid #e2e8f0',
                paddingBottom: 14,
            }}
        >
            <div
                style={{
                    color: '#667085',
                    fontSize: 15,
                    fontWeight: 600,
                }}
            >
                {label}
            </div>

            <div
                className="mt-1"
                style={{
                    color: '#111827',
                    fontSize: 22,
                    fontWeight: 700,
                }}
            >
                {value}
            </div>
        </div>
    );
}

function StatsPanel({ title, items, color }) {
    return (
        <section
            style={{
                backgroundColor: '#fff',
                border: '1px solid #d9e1ea',
                borderRadius: 28,
                boxShadow: '0 8px 32px rgba(15, 76, 129, 0.05)',
                padding: 24,
            }}
        >
            <h2
                style={{
                    color,
                    fontSize: 34,
                    fontWeight: 800,
                    marginBottom: 18,
                }}
            >
                {title}
            </h2>

            <div
                style={{
                    border: '1px solid #d9e1ea',
                    borderRadius: 20,
                    overflow: 'hidden',
                }}
            >
                {items.map(([number, value], index) => (
                    <div
                        key={number}
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'space-between',
                            gap: 16,
                            padding: '18px 22px',
                            backgroundColor: '#fff',
                            borderBottom:
                                index === items.length - 1 ? 'none' : '1px solid #d9e1ea',
                        }}
                    >
                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 18,
                            }}
                        >
                            <div
                                style={{
                                    width: 64,
                                    height: 64,
                                    minWidth: 64,
                                    minHeight: 64,
                                    borderRadius: '9999px',
                                    backgroundColor: color,
                                    color: '#fff',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    fontSize: 30,
                                    fontWeight: 500,
                                    lineHeight: 1,
                                }}
                            >
                                {index + 1}
                            </div>

                            <div
                                style={{
                                    width: 74,
                                    height: 74,
                                    minWidth: 74,
                                    minHeight: 74,
                                    borderRadius: '9999px',
                                    border: `5px solid ${color}`,
                                    backgroundColor: '#fff',
                                    color: '#000',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    fontSize: 34,
                                    fontWeight: 700,
                                    lineHeight: 1,
                                }}
                            >
                                {number}
                            </div>
                        </div>

                        <div
                            style={{
                                color,
                                fontSize: 54,
                                fontWeight: 500,
                                lineHeight: 1,
                            }}
                        >
                            {value}
                        </div>
                    </div>
                ))}
            </div>
        </section>
    );
}
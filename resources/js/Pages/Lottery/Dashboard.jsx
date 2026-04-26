import { Link, router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import {
    BarChart,
    Bar,
    CartesianGrid,
    XAxis,
    YAxis,
    Tooltip,
    ResponsiveContainer,
} from 'recharts';
import {
    ActionLink,
    HeroBanner,
    LotteryPage,
    MetricCard,
    NumberBall,
    SectionHeading,
    SurfaceCard,
    Tag,
    lotteryPalette,
} from './components/LotteryUi';
import { getImportHelp, getPlayInstruction, supportsCaixaOperations } from './components/modalityRules';

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
    suggestedNextContestNumber = 1,
    dashboardNarrative = null,
    latestDrawExplanation = null,
}) {
    const [selectedWindow, setSelectedWindow] = useState('all');
    const [insights, setInsights] = useState({
        dashboardNarrative,
        latestDrawExplanation,
    });
    const [insightsLoading, setInsightsLoading] = useState(false);
    const { flash = {}, auth = {} } = usePage().props;
    const importForm = useForm({ spreadsheet: null });
    const dataHojeISO = () => new Date().toISOString().slice(0, 10);

    const manualResultForm = useForm({
        contest_number: suggestedNextContestNumber || '',
        draw_date: dataHojeISO(),
        numbers: Array.from({ length: modality.draw_count }, () => ''),
        observation: '',
    });


    useEffect(() => {
        setInsights({
            dashboardNarrative,
            latestDrawExplanation,
        });
    }, [dashboardNarrative, latestDrawExplanation, modality.id]);

   useEffect(() => {
    let isMounted = true;
    setInsightsLoading(true);

    fetch(`/lottery/modalities/${modality.id}/dashboard-insights`, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
        .then(async (response) => {
            if (!response.ok) {
                throw new Error('Falha ao buscar insights do dashboard.');
            }

            return response.json();
        })
        .then((data) => {
            if (!isMounted) {
                return;
            }

            setInsights(data);
        })
        .catch(() => {
            if (!isMounted) {
                return;
            }

            setInsights({
                latestDrawExplanation: null,
                dashboardNarrative: null,
            });
        })
        .finally(() => {
            if (!isMounted) {
                return;
            }

            setInsightsLoading(false);
        });

    return () => {
        isMounted = false;
    };
}, [modality.id]);

    const resolvedDashboardNarrative = insights.dashboardNarrative;
    const resolvedLatestDrawExplanation = insights.latestDrawExplanation;

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

    const topFrequencies = Object.entries(selectedFrequencies || {})
        .sort((a, b) => b[1] - a[1])
        .slice(0, 8);

    const topDelays = Object.entries(selectedDelays || {})
        .sort((a, b) => b[1] - a[1])
        .slice(0, 8);

    const chartData = Object.entries(selectedFrequencies || {}).map(([number, total]) => ({
        number: String(number).padStart(2, '0'),
        total,
    }));

    const latestDraw = recentDraws.length > 0 ? recentDraws[0] : null;
    const recentNumbers = latestDraw?.numbers || [];
    const canManageCaixa = supportsCaixaOperations(modality);

    const submitImport = (event) => {
        event.preventDefault();

        importForm.post(`/lottery/modalities/${modality.id}/import-spreadsheet`, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => importForm.reset('spreadsheet'),
        });
    };

    const updateManualNumber = (index, value) => {
    const nextNumbers = [...manualResultForm.data.numbers];
    nextNumbers[index] = value;
    manualResultForm.setData('numbers', nextNumbers);
};

    const submitManualResult = (event) => {
        event.preventDefault();

        manualResultForm.transform((data) => ({
            ...data,
            contest_number: Number(data.contest_number),
            numbers: (data.numbers || []).map((value) => Number(value)),
        }));

        manualResultForm.post(`/lottery/modalities/${modality.id}/draws`, {
            preserveScroll: true,
            onSuccess: () => {
                manualResultForm.setData({
                    contest_number: Number(manualResultForm.data.contest_number) + 1,
                    draw_date: dataHojeISO(),
                    numbers: Array.from({ length: modality.draw_count }, () => ''),
                    observation: '',
                });
            },
            onFinish: () => manualResultForm.transform((data) => data),
        });
    };

    const windowOptions = [
        { value: 'all', label: 'Histórico total' },
        { value: '10', label: 'Últimos 10' },
        { value: '20', label: 'Últimos 20' },
        { value: '50', label: 'Últimos 50' },
    ];

    return (
        <LotteryPage>
            <div className="space-y-8 md:space-y-10">
                <HeroBanner
                    eyebrow="Resultado oficial"
                    title={modality.name}
                    contest={latestContestNumber ? `Concurso ${latestContestNumber}` : undefined}
                    subtitle={
                        auth?.user
                            ? getPlayInstruction(modality)
                            : 'Resultados públicos com uma apresentação mais rica. Para salvar combinações e apostar, entre na sua conta.'
                    }
                    modality={modality}
                >
                    <ActionLink href={`/lottery/modalities/${modality.id}/history`}>
                        Ver histórico completo
                    </ActionLink>
                    <ActionLink href={`/lottery/modalities/${modality.id}/play`} variant="secondary">
                        Gerar e analisar jogo
                    </ActionLink>
                    <ActionLink
                        href={auth?.user ? `/lottery/modalities/${modality.id}/bets` : '/login'}
                        variant="secondary"
                    >
                        {auth?.user ? 'Minha área' : 'Entrar'}
                    </ActionLink>
                    <ActionLink href={`/lottery/modalities/${modality.id}/repeated-combinations`}>
                        Combinações repetidas
                    </ActionLink>
                </HeroBanner>

                {flash.success ? (
                    <div className="rounded-[24px] border border-emerald-200 bg-emerald-50 px-5 py-4 font-semibold text-emerald-700">
                        {flash.success}
                    </div>
                ) : null}

                {flash.error ? (
                    <div className="rounded-[24px] border border-rose-200 bg-rose-50 px-5 py-4 font-semibold text-rose-700">
                        {flash.error}
                    </div>
                ) : null}

                <div className="grid gap-4 md:grid-cols-3">
                    <MetricCard label="Total de concursos" value={totalDraws} />
                    <MetricCard label="Concurso mais recente" value={latestContestNumber ?? '—'} />
                    <MetricCard
                        label="Faixa da modalidade"
                        value={`${modality.min_number}-${modality.max_number}`}
                        helper={`${modality.draw_count} dezenas por sorteio`}
                    />
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <SurfaceCard>
                        <SectionHeading
                            eyebrow="Último resultado"
                            title={`Concurso ${latestDraw?.contest_number ?? latestContestNumber ?? '—'}`}
                            description="Concurso atual, números sorteados e leitura rápida automatizada do resultado oficial."
                            aside={
                                <Link
                                    href={`/lottery/modalities/${modality.id}/history`}
                                    className="inline-flex min-h-[46px] items-center justify-center rounded-2xl border px-4 py-2 text-sm font-semibold"
                                    style={{ borderColor: lotteryPalette.line, color: lotteryPalette.blue }}
                                >
                                    Ver histórico completo
                                </Link>
                            }
                        />

                        {latestDraw ? (
                            <div className="space-y-5 rounded-[26px] border p-5 md:p-7" style={{ borderColor: lotteryPalette.line, background: 'linear-gradient(180deg, #ffffff 0%, #f8fbff 100%)' }}>
                                <div className="text-sm font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                    {latestDraw.draw_date}
                                </div>

                                <div className="flex flex-wrap items-center gap-3">
                                    {recentNumbers.map((number) => (
                                        <NumberBall key={number} number={number} size="lg" />
                                    ))}
                                </div>

                                {resolvedLatestDrawExplanation ? (
                                    <div
                                        className="rounded-[22px] border px-5 py-4 text-base leading-8 md:text-lg"
                                        style={{ borderColor: lotteryPalette.line, backgroundColor: lotteryPalette.soft, color: '#23354d' }}
                                    >
                                        <div className="rounded-2xl border border-slate-200 bg-white/90 p-5 shadow-sm">
                                            {resolvedLatestDrawExplanation.summary ? (
                                                <p className="text-base leading-7 text-slate-700">
                                                    {resolvedLatestDrawExplanation.summary}
                                                </p>
                                            ) : null}

                                            {Array.isArray(resolvedLatestDrawExplanation.highlights) && resolvedLatestDrawExplanation.highlights.length > 0 ? (
                                                <ul className="mt-4 space-y-2 text-sm text-slate-600">
                                                    {resolvedLatestDrawExplanation.highlights.map((item, index) => (
                                                        <li key={index}>• {item}</li>
                                                    ))}
                                                </ul>
                                            ) : null}
                                        </div>
                                    </div>
                                ) : insightsLoading ? (
                                    <div className="rounded-2xl border border-slate-200 bg-white/80 p-5 text-sm text-slate-500 shadow-sm">
                                        Carregando leitura analítica do último concurso...
                                    </div>
                                ) : null}
                            </div>
                        ) : (
                            <div className="rounded-[24px] border px-5 py-8" style={{ borderColor: lotteryPalette.line, color: lotteryPalette.muted }}>
                                Ainda não há resultado carregado para esta modalidade.
                            </div>
                        )}
                    </SurfaceCard>

                    <SurfaceCard>
                        <SectionHeading
                            eyebrow="Ações rápidas"
                            title="Gestão da modalidade"
                            description="Importe planilhas oficiais, sincronize novos concursos e mantenha o painel sempre atualizado."
                        />

                        <div className="space-y-4">
                            {canManageCaixa ? (
                                <button
                                    type="button"
                                    onClick={() => router.post(`/lottery/modalities/${modality.id}/sync-results`)}
                                    className="inline-flex w-full items-center justify-center rounded-2xl px-5 py-4 text-base font-semibold text-white shadow-[0_18px_30px_rgba(12,90,150,0.18)]"
                                    style={{ background: 'linear-gradient(180deg, #1670b6 0%, #0c5a96 100%)' }}
                                >
                                    Sincronizar resultados da CAIXA
                                </button>
                            ) : (
                                <div className="rounded-[24px] border px-5 py-4 text-sm leading-7" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fafcff', color: lotteryPalette.muted }}>
                                    A sincronização automática pela CAIXA ainda não está disponível para {modality.name}.
                                </div>
                            )}

                            <form onSubmit={submitImport} className="rounded-[24px] border p-4 md:p-5" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fafcff' }}>
                                <label className="block text-sm font-semibold" style={{ color: lotteryPalette.blueDark }}>
                                    Enviar planilha oficial
                                </label>
                                <p className="mt-1 text-sm" style={{ color: lotteryPalette.muted }}>
                                    {getImportHelp(modality)}
                                </p>
                                <input
                                    type="file"
                                    accept=".xlsx,.xls"
                                    onChange={(event) => importForm.setData('spreadsheet', event.target.files?.[0] || null)}
                                    className="mt-4 block w-full rounded-2xl border bg-white px-4 py-3 text-sm"
                                    style={{ borderColor: lotteryPalette.line }}
                                />
                                {importForm.errors.spreadsheet ? (
                                    <div className="mt-2 text-sm font-medium text-rose-600">
                                        {importForm.errors.spreadsheet}
                                    </div>
                                ) : null}
                                <button
                                    type="submit"
                                    className="mt-4 inline-flex w-full items-center justify-center rounded-2xl border px-5 py-3 font-semibold"
                                    style={{ borderColor: lotteryPalette.line, color: lotteryPalette.blue, backgroundColor: '#fff' }}
                                    disabled={importForm.processing || !canManageCaixa}
                                >
                                    {importForm.processing ? 'Importando...' : 'Importar planilha'}
                                </button>
                            </form>
                            <form onSubmit={submitManualResult} className="rounded-[24px] border p-4 md:p-5" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fafcff' }}>
                            <label className="block text-sm font-semibold" style={{ color: lotteryPalette.blueDark }}>
                                Cadastrar resultado individual
                            </label>
                            <p className="mt-1 text-sm" style={{ color: lotteryPalette.muted }}>
                                Lance um concurso manualmente com os mesmos campos essenciais do fluxo oficial. Sugestão de próximo concurso: {suggestedNextContestNumber}.
                            </p>

                            <div className="mt-4 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                        Concurso
                                    </label>
                                    <input
                                        type="number"
                                        min={1}
                                        value={manualResultForm.data.contest_number}
                                        onChange={(event) => manualResultForm.setData('contest_number', event.target.value)}
                                        className="mt-2 block w-full rounded-2xl border bg-white px-4 py-3 text-sm"
                                        style={{ borderColor: lotteryPalette.line }}
                                    />
                                    {manualResultForm.errors.contest_number ? (
                                        <div className="mt-2 text-sm font-medium text-rose-600">
                                            {manualResultForm.errors.contest_number}
                                        </div>
                                    ) : null}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                        Data do sorteio
                                    </label>
                                    <input
                                        type="date"
                                        value={manualResultForm.data.draw_date}
                                        onChange={(event) => manualResultForm.setData('draw_date', event.target.value)}
                                        className="mt-2 block w-full rounded-2xl border bg-white px-4 py-3 text-sm"
                                        style={{ borderColor: lotteryPalette.line }}
                                    />
                                    {manualResultForm.errors.draw_date ? (
                                        <div className="mt-2 text-sm font-medium text-rose-600">
                                            {manualResultForm.errors.draw_date}
                                        </div>
                                    ) : null}
                                </div>
                            </div>

                            <div className="mt-4">
                                <label className="block text-xs font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                    Números sorteados
                                </label>

                               <div className="mt-3 flex flex-wrap gap-2">
                                    {manualResultForm.data.numbers.map((value, index) => (
                                        <div key={index} className="flex flex-col items-center">
                                            <input
                                                type="number"
                                                min={modality.min_number}
                                                max={modality.max_number}
                                                value={value}
                                                onChange={(event) => updateManualNumber(index, event.target.value)}
                                                placeholder={String(index + 1)}
                                                className="h-12 w-16 rounded-xl border bg-white px-2 text-center text-base font-semibold"
                                                style={{ borderColor: lotteryPalette.line, color: lotteryPalette.blueDark }}
                                            />
                                            {manualResultForm.errors[`numbers.${index}`] ? (
                                                <div className="mt-1 max-w-[64px] text-center text-[11px] font-medium leading-4 text-rose-600">
                                                    {manualResultForm.errors[`numbers.${index}`]}
                                                </div>
                                            ) : null}
                                        </div>
                                    ))}
                                </div>

                                {manualResultForm.errors.numbers ? (
                                    <div className="mt-2 text-sm font-medium text-rose-600">
                                        {manualResultForm.errors.numbers}
                                    </div>
                                ) : null}
                            </div>

                            <div className="mt-4">
                                <label className="block text-xs font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                    Observação (opcional)
                                </label>
                                <textarea
                                    value={manualResultForm.data.observation}
                                    onChange={(event) => manualResultForm.setData('observation', event.target.value)}
                                    rows={3}
                                    className="mt-2 block w-full rounded-2xl border bg-white px-4 py-3 text-sm"
                                    style={{ borderColor: lotteryPalette.line }}
                                    placeholder="Ex.: concurso lançado manualmente para correção interna."
                                />
                                {manualResultForm.errors.observation ? (
                                    <div className="mt-2 text-sm font-medium text-rose-600">
                                        {manualResultForm.errors.observation}
                                    </div>
                                ) : null}
                            </div>

                            <button
                                type="submit"
                                className="mt-4 inline-flex w-full items-center justify-center rounded-2xl border px-5 py-3 font-semibold"
                                style={{ borderColor: lotteryPalette.line, color: lotteryPalette.blue, backgroundColor: '#fff' }}
                                disabled={manualResultForm.processing}
                            >
                                {manualResultForm.processing ? 'Salvando resultado...' : 'Cadastrar resultado manual'}
                            </button>
                        </form>
                        </div>
                    </SurfaceCard>
                </div>

                <SurfaceCard>
                    <SectionHeading
                        eyebrow="Resumo rápido"
                        title={`Leitura automática da ${modality.name}`}
                        description={
                            typeof resolvedDashboardNarrative === 'string'
                                ? resolvedDashboardNarrative
                                : resolvedDashboardNarrative?.summary ||
                                  resolvedDashboardNarrative?.headline ||
                                  (insightsLoading
                                      ? 'Carregando leitura estratégica completa desta modalidade...'
                                      : 'Frequências recentes, atrasos e comportamento estatístico organizados de forma mais visual.')
                        }
                    />

                    <div className="flex flex-wrap gap-3">
                        {windowOptions.map((option) => (
                            <Tag
                                key={option.value}
                                active={selectedWindow === option.value}
                                onClick={() => setSelectedWindow(option.value)}
                            >
                                {option.label}
                            </Tag>
                        ))}
                    </div>

                    <div className="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                        <div className="rounded-[24px] border p-4 md:p-5" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff' }}>
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <div>
                                    <div className="text-sm font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                        Frequência visual
                                    </div>
                                    <div className="mt-1 text-xl font-bold" style={{ color: lotteryPalette.blueDark }}>
                                        Números mais presentes
                                    </div>
                                </div>
                            </div>

                            <div className="h-[320px] md:h-[360px]">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart data={chartData}>
                                        <CartesianGrid strokeDasharray="3 3" stroke="#e4edf5" />
                                        <XAxis dataKey="number" tick={{ fill: '#667085', fontSize: 12 }} />
                                        <YAxis tick={{ fill: '#667085', fontSize: 12 }} />
                                        <Tooltip />
                                        <Bar dataKey="total" fill="#0c5a96" radius={[8, 8, 0, 0]} />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        </div>

                        <div className="space-y-4">
                            <div className="rounded-[24px] border p-5" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff' }}>
                                <div className="text-sm font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                    Frequência recente em destaque
                                </div>
                                <div className="mt-4 space-y-3">
                                    {topFrequencies.map(([number, total]) => (
                                        <div key={number} className="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3" style={{ borderColor: lotteryPalette.line }}>
                                            <div className="flex items-center gap-3">
                                                <NumberBall number={number} size="sm" />
                                                <div className="font-semibold" style={{ color: lotteryPalette.blueDark }}>
                                                    Número {String(number).padStart(2, '0')}
                                                </div>
                                            </div>
                                            <div className="text-lg font-bold" style={{ color: lotteryPalette.blue }}>
                                                {total}x
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="rounded-[24px] border p-5" style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff' }}>
                                <div className="text-sm font-semibold uppercase tracking-[0.18em]" style={{ color: lotteryPalette.muted }}>
                                    Atrasos relevantes
                                </div>
                                <div className="mt-4 space-y-3">
                                    {topDelays.map(([number, total]) => (
                                        <div key={number} className="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3" style={{ borderColor: lotteryPalette.line }}>
                                            <div className="flex items-center gap-3">
                                                <NumberBall number={number} size="sm" subtle />
                                                <div className="font-semibold" style={{ color: lotteryPalette.blueDark }}>
                                                    Número {String(number).padStart(2, '0')}
                                                </div>
                                            </div>
                                            <div className="text-lg font-bold" style={{ color: '#c2410c' }}>
                                                {total}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </SurfaceCard>
            </div>
        </LotteryPage>
    );
}

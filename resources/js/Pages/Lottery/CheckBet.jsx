import { Link } from '@inertiajs/react';

function Ball({ number, filled = false, hit = false }) {
    return (
        <div
            style={{
                width: 52,
                height: 52,
                borderRadius: '9999px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontWeight: 700,
                fontSize: 18,
                backgroundColor: filled || hit ? '#2f35a8' : '#fff',
                color: filled || hit ? '#fff' : '#8b8b8b',
                border: `2px solid ${hit ? '#2f35a8' : '#b8b8b8'}`,
            }}
        >
            {String(number).padStart(2, '0')}
        </div>
    );
}

export default function CheckBet({ modality, historyItem, latestContestNumber = null, officialResult = null }) {
    const quinaBlue = '#0c5a96';
    const quinaMuted = '#667085';
    const hasOfficialResult = Boolean(officialResult?.official_numbers?.length);
    const hitSet = new Set(officialResult?.hits || []);

    return (
        <div className="min-h-screen bg-slate-50 py-8 px-4">
            <div className="max-w-6xl mx-auto space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <Link
                        href={`/lottery/modalities/${modality.id}/combination-history`}
                        className="px-4 py-2 rounded-xl border bg-white"
                    >
                        Voltar ao histórico
                    </Link>

                    <Link
                        href={`/lottery/modalities/${modality.id}/play?history_id=${historyItem.id}`}
                        className="px-4 py-2 rounded-xl border bg-white"
                    >
                        Reabrir combinação
                    </Link>
                </div>

                <div className="overflow-hidden rounded-[32px] border bg-white shadow-sm">
                    <div className="bg-[#2f35a8] px-6 py-5 flex items-center justify-between gap-3">
                        <div>
                            <h1 className="text-white text-3xl font-extrabold">
                                Conferir jogo {modality.name.toLowerCase()}
                            </h1>
                            <p className="text-white/80 mt-1">
                                Concurso atual: {latestContestNumber || '—'}
                            </p>
                        </div>
                    </div>

                    <div className="p-6 md:p-8 space-y-8">
                        {!historyItem.bet_contest_number ? (
                            <div className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
                                Esta combinação ainda não foi vinculada a um concurso. Vá ao histórico ou à tela de análise e clique em realizar aposta.
                            </div>
                        ) : null}

                        {historyItem.bet_contest_number ? (
                            <div className="text-slate-700 text-lg">
                                Concurso da aposta: <strong>{historyItem.bet_contest_number}</strong>
                                {historyItem.bet_registered_at ? ` • registrada em ${historyItem.bet_registered_at}` : ''}
                            </div>
                        ) : null}

                        {hasOfficialResult ? (
                            <div className="grid gap-8 lg:grid-cols-2">
                                <div className="space-y-4">
                                    <div>
                                        <div className="text-2xl font-bold" style={{ color: quinaBlue }}>
                                            Números sorteados
                                        </div>
                                        <div className="text-[20px] mt-2" style={{ color: quinaMuted }}>
                                            Concurso: {officialResult.contest_number}
                                        </div>
                                    </div>

                                    <div className="flex flex-wrap gap-3">
                                        {officialResult.official_numbers.map((number) => (
                                            <Ball key={`official-${number}`} number={number} filled />
                                        ))}
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <div>
                                        <div className="text-2xl font-bold" style={{ color: quinaBlue }}>
                                            Seus números
                                        </div>
                                        <div className="text-[20px] mt-2" style={{ color: quinaMuted }}>
                                            Concurso: {officialResult.contest_number}
                                        </div>
                                    </div>

                                    <div className="flex flex-wrap gap-3">
                                        {officialResult.user_numbers.map((number) => (
                                            <Ball
                                                key={`user-${number}`}
                                                number={number}
                                                hit={hitSet.has(number)}
                                            />
                                        ))}
                                    </div>

                                    <div className="text-[20px]" style={{ color: quinaMuted }}>
                                        Acertos: <strong style={{ color: '#344054' }}>{officialResult.hit_label}</strong>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-slate-700">
                                Ainda não existe resultado oficial cadastrado para o concurso {historyItem.bet_contest_number}. Assim que o resultado entrar no sistema, esta tela mostrará os acertos automaticamente.
                            </div>
                        )}

                        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div className="text-lg font-bold" style={{ color: quinaBlue }}>
                                Sua aposta registrada
                            </div>
                            <div className="flex flex-wrap gap-3 mt-4">
                                {historyItem.numbers.map((number) => (
                                    <Ball key={`registered-${number}`} number={number} />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

import { Link } from '@inertiajs/react';

function Ball({ number, active = false }) {
    return (
        <div
            className="w-12 h-12 rounded-full border flex items-center justify-center font-bold text-lg"
            style={{
                backgroundColor: active ? '#2f3ca5' : '#fff',
                color: active ? '#fff' : '#6b7280',
                borderColor: '#9ca3af',
            }}
        >
            {String(number).padStart(2, '0')}
        </div>
    );
}

export default function CheckBet({ modality, historyItem, officialResult, checkResult }) {
    const hits = checkResult?.hits || [];

    return (
        <div className="max-w-6xl mx-auto p-6 space-y-8">
            <div className="flex items-center justify-between gap-3 flex-wrap">
                <h1 className="text-3xl font-extrabold" style={{ color: '#0c5a96' }}>
                    Conferir jogo — {modality.name}
                </h1>

                <div className="flex gap-3 flex-wrap">
                    <Link href={`/lottery/modalities/${modality.id}/combination-history`} className="px-4 py-2 rounded border">
                        Voltar ao histórico
                    </Link>
                    <Link href="/lottery/my-bets" className="px-4 py-2 rounded border">
                        Minhas apostas
                    </Link>
                </div>
            </div>

            <div className="rounded-2xl border bg-white overflow-hidden">
                <div className="px-6 py-4 text-white font-bold text-2xl" style={{ backgroundColor: '#2f3ca5' }}>
                    Resultado {modality.code}
                </div>

                <div className="grid md:grid-cols-2 gap-8 p-6">
                    <div className="space-y-6">
                        <div>
                            <div className="text-2xl font-semibold text-slate-700">Concurso atual: {officialResult.contest_number}</div>
                        </div>

                        <div className="space-y-3">
                            <div className="text-xl font-bold" style={{ color: '#0c5a96' }}>Números sorteados</div>
                            <div className="text-2xl font-semibold text-slate-700">Concurso: {officialResult.contest_number}</div>
                            <div className="flex flex-wrap gap-3">
                                {officialResult.numbers.map((number) => (
                                    <Ball key={number} number={number} active />
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="space-y-6">
                        <div className="text-xl font-bold" style={{ color: '#0c5a96' }}>Seus números</div>
                        <div className="text-2xl font-semibold text-slate-700">Concurso: {historyItem.bet_contest_number}</div>
                        <div className="flex flex-wrap gap-3">
                            {historyItem.numbers.map((number) => (
                                <Ball key={number} number={number} active={hits.includes(number)} />
                            ))}
                        </div>
                        <div className="text-2xl text-slate-700">
                            Acertos: <strong>{checkResult.hit_count === 0 ? 'nenhum' : `${checkResult.hit_count} ${checkResult.hit_count === 1 ? 'número' : 'números'}`}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

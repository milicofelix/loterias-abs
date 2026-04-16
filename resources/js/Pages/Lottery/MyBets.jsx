import { Link, router } from '@inertiajs/react';
import { NumberBall } from './components/LotteryUi';

export default function MyBets({ items, filters = {}, dayOptions = [7, 15, 30, 60, 90] }) {
    return (
        <div className="max-w-6xl mx-auto p-6 space-y-6">
            <div className="flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h1 className="text-3xl font-extrabold" style={{ color: '#0c5a96' }}>
                        Minhas apostas
                    </h1>
                    <p className="text-slate-500 mt-1">
                        Consulte suas apostas mais recentes por período.
                    </p>
                </div>

                <Link href="/lottery/modalities" className="px-4 py-2 rounded border">
                    Voltar para modalidades
                </Link>
            </div>

            <div className="flex gap-2 flex-wrap">
                {dayOptions.map((days) => {
                    const active = Number(filters.days) === Number(days);

                    return (
                        <button
                            key={days}
                            type="button"
                            onClick={() =>
                                router.get('/lottery/my-bets', { days }, { preserveState: true, replace: true })
                            }
                            className="px-4 py-2 rounded-xl border font-semibold"
                            style={{
                                backgroundColor: active ? '#0c5a96' : '#fff',
                                color: active ? '#fff' : '#0c5a96',
                                borderColor: '#cfd8e3',
                            }}
                        >
                            Últimos {days} dias
                        </button>
                    );
                })}
            </div>

            <div className="space-y-4">
                {items.data.length === 0 ? (
                    <div className="rounded-2xl border bg-white p-6 text-slate-500">
                        Nenhuma aposta encontrada para o período selecionado.
                    </div>
                ) : (
                    items.data.map((item) => (
                        <div key={item.id} className="rounded-2xl border bg-white p-5 space-y-4">
                            <div className="flex items-center justify-between gap-3 flex-wrap">
                                <div>
                                    <div className="text-lg font-bold text-slate-800">
                                        {item.modality?.name}
                                    </div>
                                    <div className="text-slate-500">
                                        Concurso {item.bet_contest_number} · {item.bet_registered_at}
                                    </div>
                                </div>

                                {item.modality?.id && (
                                    <Link
                                        href={`/lottery/modalities/${item.modality.id}/combination-history/${item.id}/check-bet`}
                                        className="px-4 py-2 rounded-xl border font-semibold"
                                    >
                                        Conferir jogo
                                    </Link>
                                )}
                            </div>

                            <div className="flex flex-wrap gap-2">
                                {item.numbers.map((number) => (
                                    <NumberBall key={number} number={number} size="sm" />
                                ))}
                            </div>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
}
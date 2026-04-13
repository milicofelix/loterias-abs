import axios from 'axios';
import { Link, router, usePage } from '@inertiajs/react';

export default function CombinationHistory({ modality, items, filters }) {
    const authUser = usePage().props.auth?.user;
    const quinaBlue = '#0c5a96';
    const quinaBorder = '#d9e1ea';
    const quinaMuted = '#667085';

    function applySourceFilter(source) {
        router.get(
            `/lottery/modalities/${modality.id}/combination-history`,
            { source },
            { preserveState: true, replace: true }
        );
    }

    function handleDelete(itemId) {
        if (!window.confirm('Deseja remover este item do histórico?')) {
            return;
        }

        router.delete(`/lottery/modalities/${modality.id}/combination-history/${itemId}`);
    }

    function handleClearAll() {
        if (!window.confirm('Deseja limpar todo o histórico desta modalidade?')) {
            return;
        }

        router.delete(`/lottery/modalities/${modality.id}/combination-history`);
    }

    async function handleRegisterBet(itemId) {
        try {
            await axios.post(`/lottery/modalities/${modality.id}/combination-history/${itemId}/register-bet`);
            window.location.reload();
        } catch (error) {
            if (error?.response?.status === 401) {
                window.location.href = '/login';
                return;
            }

            window.alert(error?.response?.data?.message || 'Não foi possível registrar a aposta.');
        }
    }

    return (
        <div className="max-w-7xl mx-auto px-4 md:px-8 mt-4 md:mt-10 pb-8 md:pb-10 space-y-6 md:space-y-8">
            <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div className="max-w-3xl">
                    <h1 style={{ color: quinaBlue, fontWeight: 800 }} className="text-[2rem] leading-tight md:text-[34px]">
                        Histórico de combinações — {modality.name}
                    </h1>

                    <p className="mt-2 md:mt-3 text-base md:text-[20px]" style={{ color: quinaMuted, fontWeight: 500, lineHeight: 1.5 }}>
                        Consulte, reabra e gerencie as combinações analisadas no sistema.
                    </p>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full lg:w-auto lg:flex lg:flex-wrap lg:items-center lg:self-auto">
                    <Link
                        href={`/lottery/modalities/${modality.id}/play`}
                        className="inline-flex h-12 md:h-14 w-full sm:w-auto items-center justify-center px-5 md:px-6 rounded-2xl border bg-white font-semibold text-center leading-none min-w-0 sm:min-w-[190px]"
                        style={{ borderColor: quinaBorder }}
                    >
                        Nova análise
                    </Link>

                    {authUser ? (
                        <Link
                            href="/lottery/my-bets"
                            className="inline-flex h-12 md:h-14 w-full sm:w-auto items-center justify-center px-5 md:px-6 rounded-2xl border bg-white font-semibold text-center leading-none min-w-0 sm:min-w-[190px]"
                            style={{ borderColor: quinaBorder }}
                        >
                            Minhas apostas
                        </Link>
                    ) : null}

                    <button
                        type="button"
                        onClick={handleClearAll}
                        className="inline-flex h-12 md:h-14 w-full sm:w-auto items-center justify-center px-5 md:px-6 rounded-2xl border font-semibold text-center leading-none min-w-0 sm:min-w-[210px]"
                        style={{ borderColor: '#f0b9b9', backgroundColor: '#fff5f5', color: '#b42318' }}
                    >
                        Limpar histórico
                    </button>
                </div>
            </div>

            <div className="flex gap-2 md:gap-3 flex-wrap">
                {[
                    { value: '', label: 'Todas' },
                    { value: 'manual', label: 'Manuais' },
                    { value: 'generated', label: 'Geradas' },
                ].map((option) => {
                    const active = (filters.source || '') === option.value;

                    return (
                        <button
                            key={option.label}
                            type="button"
                            onClick={() => applySourceFilter(option.value)}
                            className="min-h-[44px] px-4 md:px-[18px] text-sm md:text-base"
                            style={{
                                backgroundColor: active ? quinaBlue : '#fff',
                                color: active ? '#fff' : quinaBlue,
                                border: `1px solid ${active ? quinaBlue : quinaBorder}`,
                                borderRadius: 16,
                                fontWeight: 700,
                                minWidth: 104,
                            }}
                        >
                            {option.label}
                        </button>
                    );
                })}
            </div>

            <div className="space-y-4 md:space-y-5">
                {items.data.length === 0 ? (
                    <div className="rounded-3xl bg-white p-6 md:p-8" style={{ border: `1px solid ${quinaBorder}`, color: quinaMuted, fontSize: 16 }}>
                        Nenhuma combinação encontrada.
                    </div>
                ) : (
                    items.data.map((item) => (
                        <div
                            key={item.id}
                            className="rounded-3xl bg-white p-4 md:p-7"
                            style={{ border: `1px solid ${quinaBorder}`, boxShadow: '0 8px 28px rgba(15, 76, 129, 0.05)' }}
                        >
                            <div className="flex flex-col gap-4 md:gap-5">
                                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div className="space-y-2">
                                        <div className="text-[16px] md:text-[18px]" style={{ color: '#101828', fontWeight: 600 }}>
                                            {item.created_at}
                                        </div>

                                        <div className="text-[15px] md:text-[16px]" style={{ color: quinaMuted }}>
                                            Origem: <strong style={{ color: '#101828' }}>{item.source === 'manual' ? 'Manual' : 'Gerada'}</strong>
                                        </div>

                                        {item.bet_contest_number ? (
                                            <div className="text-[15px] md:text-[16px]" style={{ color: quinaMuted }}>
                                                Aposta vinculada ao concurso <strong style={{ color: '#101828' }}>{item.bet_contest_number}</strong>
                                            </div>
                                        ) : null}

                                        {item.analysis_snapshot?.score ? (
                                            <div className="mt-2 inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold" style={{ backgroundColor: '#eef6ff', color: quinaBlue, border: `1px solid ${quinaBorder}` }}>
                                                Score {item.analysis_snapshot.score.value}/100 · {item.analysis_snapshot.score.label}
                                            </div>
                                        ) : null}
                                    </div>

                                    <div className="grid grid-cols-2 gap-2 w-full sm:w-auto sm:flex sm:flex-wrap">
                                        <button
                                            type="button"
                                            onClick={() => router.visit(`/lottery/modalities/${modality.id}/play?history_id=${item.id}`)}
                                            className="inline-flex h-11 md:h-12 items-center justify-center px-4 rounded-xl border text-sm font-semibold bg-white leading-none w-full sm:w-auto"
                                            style={{ borderColor: quinaBorder }}
                                        >
                                            Reanalisar
                                        </button>

                                        {!item.bet_contest_number ? (
                                            <button
                                                type="button"
                                                onClick={() => handleRegisterBet(item.id)}
                                                className="inline-flex h-11 md:h-12 items-center justify-center px-4 rounded-xl border text-sm font-semibold bg-white leading-none w-full sm:w-auto"
                                                style={{ borderColor: quinaBorder }}
                                            >
                                                Realizar aposta
                                            </button>
                                        ) : authUser ? (
                                            <Link
                                                href={`/lottery/modalities/${modality.id}/combination-history/${item.id}/check-bet`}
                                                className="inline-flex h-11 md:h-12 items-center justify-center px-4 rounded-xl border text-sm font-semibold bg-white leading-none w-full sm:w-auto"
                                                style={{ borderColor: quinaBorder }}
                                            >
                                                Conferir jogo
                                            </Link>
                                        ) : null}

                                        <button
                                            type="button"
                                            onClick={() => handleDelete(item.id)}
                                            className="inline-flex h-11 md:h-12 items-center justify-center px-4 rounded-xl border text-sm font-semibold leading-none w-full sm:w-auto"
                                            style={{ borderColor: '#f0b9b9', backgroundColor: '#fff5f5', color: '#b42318' }}
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </div>

                                <div className="flex flex-wrap items-center gap-2 md:gap-3">
                                    {item.numbers.map((number) => (
                                        <div
                                            key={number}
                                            style={{
                                                width: 48,
                                                height: 48,
                                                borderRadius: '9999px',
                                                backgroundColor: quinaBlue,
                                                color: '#fff',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                                fontWeight: 700,
                                                fontSize: 22,
                                                boxShadow: '0 8px 18px rgba(12, 90, 150, 0.18)',
                                            }}
                                            className="md:w-[56px] md:h-[56px] md:text-[24px]"
                                        >
                                            {String(number).padStart(2, '0')}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
}

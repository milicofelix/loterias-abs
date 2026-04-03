import { router } from '@inertiajs/react';
import { useState } from 'react';

export default function History({ modality, draws, filters }) {
    const [search, setSearch] = useState(filters.q || '');

    const quinaBlue = '#0c5a96';
    const quinaBall = '#0f4c81';

    function handleSearch(e) {
        e.preventDefault();

        router.get(
            `/lottery/modalities/${modality.id}/history`,
            { q: search },
            { preserveState: true }
        );
    }

    return (
        <div className="max-w-6xl mx-auto p-6 space-y-6">
            <h1 style={{ color: quinaBlue, fontSize: 32, fontWeight: 800 }}>
                Histórico - {modality.name}
            </h1>

            {/* BUSCA */}
            <form onSubmit={handleSearch} className="flex gap-3">
                <input
                    type="text"
                    placeholder="Buscar por concurso (ex: 6988)"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="border rounded-xl px-4 py-2 w-full"
                />

                <button
                    type="submit"
                    style={{
                        backgroundColor: quinaBlue,
                        color: '#fff',
                        padding: '10px 16px',
                        borderRadius: 12,
                        fontWeight: 600,
                    }}
                >
                    Buscar
                </button>
            </form>

            {/* LISTA */}
            <div className="space-y-4">
                {draws.data.map((draw) => (
                    <div
                        key={draw.id}
                        className="border rounded-2xl p-4 flex flex-col md:flex-row md:justify-between gap-4"
                    >
                        <div>
                            <div style={{ fontWeight: 700, color: quinaBlue }}>
                                Concurso {draw.contest_number}
                            </div>
                            <div>{draw.draw_date}</div>
                        </div>

                        <div className="flex gap-2 flex-wrap">
                            {draw.numbers.map((n) => (
                                <div
                                    key={n.number}
                                    style={{
                                        width: 44,
                                        height: 44,
                                        borderRadius: '9999px',
                                        backgroundColor: quinaBall,
                                        color: '#fff',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        fontWeight: 700,
                                    }}
                                >
                                    {String(n.number).padStart(2, '0')}
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>

            {/* PAGINAÇÃO */}
            <div className="flex gap-2 flex-wrap">
                {draws.links.map((link, i) => (
                    <button
                        key={i}
                        disabled={!link.url}
                        onClick={() => link.url && router.visit(link.url)}
                        className={`px-3 py-1 rounded ${
                            link.active ? 'bg-blue-600 text-white' : 'border'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ))}
            </div>
        </div>
    );
}
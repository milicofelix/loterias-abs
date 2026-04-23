export default function NumberPicker({
    totalNumbers,
    startNumber = 1,
    maxSelection,
    selected = [],
    onChange,
    columns = 10,
}) {
    const numbers = Array.from({ length: totalNumbers }, (_, index) => startNumber + index);

    function toggleNumber(number) {
        const exists = selected.includes(number);

        if (exists) {
            onChange(selected.filter((item) => item !== number));
            return;
        }

        if (selected.length >= maxSelection) {
            return;
        }

        onChange([...selected, number].sort((a, b) => a - b));
    }

    return (
        <div className="space-y-4">
            <div
                style={{
                    display: 'grid',
                    width: '100%',
                    gap: '12px',
                    gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
                }}
            >
                {numbers.map((number) => {
                    const active = selected.includes(number);

                    return (
                        <button
                            key={number}
                            type="button"
                            onClick={() => toggleNumber(number)}
                            className="min-h-[44px] rounded-full border text-base font-semibold transition"
                            style={{
                                width: '100%',
                                borderColor: active ? '#0c5a96' : '#b8c2d1',
                                background: active
                                    ? 'linear-gradient(180deg, #1670b6 0%, #0c5a96 100%)'
                                    : '#fff',
                                color: active ? '#fff' : '#667085',
                                boxShadow: active ? '0 10px 24px rgba(12,90,150,0.20)' : 'none',
                            }}
                        >
                            {String(number).padStart(2, '0')}
                        </button>
                    );
                })}
            </div>

            <div className="text-sm font-semibold text-slate-700">
                Selecionados: {selected.length} / {maxSelection}
            </div>
        </div>
    );
}
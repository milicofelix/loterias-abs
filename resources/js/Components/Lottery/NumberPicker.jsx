import React from 'react';

export default function NumberPicker({
    totalNumbers = 80,
    selected = [],
    onChange,
    maxSelection = 5,
    columns = 10,
}) {
    function toggleNumber(n) {
        const exists = selected.includes(n);

        if (exists) {
            onChange(selected.filter((x) => x !== n));
            return;
        }

        if (selected.length >= maxSelection) return;

        onChange([...selected, n].sort((a, b) => a - b));
    }

    return (
        <div className="space-y-4">
            <div
                className="grid gap-3 justify-items-center"
                style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}
            >
                {Array.from({ length: totalNumbers }, (_, i) => {
                    const n = i + 1;
                    const active = selected.includes(n);

                    return (
                        <button
                            key={n}
                            type="button"
                            onClick={() => toggleNumber(n)}
                            className="flex h-15 w-20 items-center justify-center rounded-full border text-base font-bold"
                            style={{
                                borderColor: active ? '#2f36b0' : '#b0b0b0',
                                backgroundColor: active ? '#2f36b0' : '#ffffff',
                                color: active ? '#ffffff' : '#8f8f8f',
                                boxShadow: active ? '0 6px 14px rgba(47, 54, 176, 0.18)' : 'none',
                            }}
                        >
                            {String(n).padStart(2, '0')}
                        </button>
                    );
                })}
            </div>

            <div className="text-base font-semibold text-slate-700">
                Selecionados: {selected.length} / {maxSelection}
            </div>
        </div>
    );
}
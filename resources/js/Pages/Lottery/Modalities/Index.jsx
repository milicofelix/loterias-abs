import { Link } from '@inertiajs/react';

export default function Index({ modalities }) {
  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-4">Modalidades</h1>

      <div className="grid gap-4">
        {modalities.map((modality) => (
          <Link
            key={modality.id}
            href={`/lottery/modalities/${modality.id}`}
            className="p-4 border rounded hover:bg-gray-50"
          >
            <div className="font-semibold">{modality.name}</div>
            <div className="text-sm text-gray-500">
              {modality.min_number} - {modality.max_number} | Sorteio: {modality.draw_count}
            </div>
          </Link>
        ))}
      </div>
    </div>
  );
}
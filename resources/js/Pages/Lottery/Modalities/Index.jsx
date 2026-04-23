import { Link } from '@inertiajs/react';
import { getPlayInstruction } from '../components/modalityRules';

export default function Index({ modalities }) {
  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-4">Modalidades</h1>

      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {modalities.map((modality) => (
          <Link
            key={modality.id}
            href={`/lottery/modalities/${modality.id}`}
            className="p-5 border rounded-2xl hover:bg-gray-50 space-y-3"
          >
            <div className="font-semibold text-lg">{modality.name}</div>
            <div className="text-sm text-gray-500">
              {modality.min_number} - {modality.max_number} | Sorteio: {modality.draw_count}
            </div>
            <p className="text-sm text-slate-600 leading-6">
              {getPlayInstruction(modality)}
            </p>
          </Link>
        ))}
      </div>
    </div>
  );
}

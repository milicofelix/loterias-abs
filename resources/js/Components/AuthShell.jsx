import { Link, usePage } from '@inertiajs/react';

export default function AuthShell({ title, subtitle, children }) {
    const { flash = {} } = usePage().props;

    return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center px-4 py-10">
            <div className="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 md:p-8 shadow-sm">
                <div className="mb-6">
                    <Link href="/" className="text-sm font-semibold text-slate-500">← Voltar</Link>
                    <h1 className="mt-4 text-3xl font-extrabold text-slate-900">{title}</h1>
                    {subtitle ? <p className="mt-2 text-slate-500">{subtitle}</p> : null}
                </div>

                {flash.error ? (
                    <div className="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {flash.error}
                    </div>
                ) : null}

                {flash.success ? (
                    <div className="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {flash.success}
                    </div>
                ) : null}

                {children}
            </div>
        </div>
    );
}

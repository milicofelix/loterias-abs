import { Head, Link, useForm } from '@inertiajs/react';
import AuthShell from '@/Components/AuthShell';

export default function Login({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(e) {
        e.preventDefault();
        post('/login');
    }

    return (
        <>
            <Head title="Entrar" />
            <AuthShell title="Entrar" subtitle="Faça login para salvar apostas, ver seu histórico privado e usar as análises inteligentes.">
                {status ? (
                    <div className="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {status}
                    </div>
                ) : null}

                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label className="mb-1 block text-sm font-semibold text-slate-700">E-mail</label>
                        <input
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            className="w-full rounded-2xl border border-slate-300 px-4 py-3"
                        />
                        {errors.email ? <div className="mt-1 text-sm text-red-600">{errors.email}</div> : null}
                    </div>

                    <div>
                        <label className="mb-1 block text-sm font-semibold text-slate-700">Senha</label>
                        <input
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="w-full rounded-2xl border border-slate-300 px-4 py-3"
                        />
                        {errors.password ? <div className="mt-1 text-sm text-red-600">{errors.password}</div> : null}
                    </div>

                    <label className="flex items-center gap-2 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        Lembrar de mim
                    </label>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-2xl bg-blue-700 px-4 py-3 font-semibold text-white"
                    >
                        {processing ? 'Entrando...' : 'Entrar'}
                    </button>
                </form>

                <div className="mt-6 text-sm text-slate-600">
                    Ainda não tem conta?{' '}
                    <Link href="/register" className="font-semibold text-blue-700">Criar conta</Link>
                </div>
            </AuthShell>
        </>
    );
}

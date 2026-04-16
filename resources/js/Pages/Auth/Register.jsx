import { Head, Link, useForm } from '@inertiajs/react';
import AuthShell from '@/Components/AuthShell';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(e) {
        e.preventDefault();

        post('/register', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    }

    return (
        <>
            <Head title="Criar conta" />

            <AuthShell
                title="Criar conta"
                subtitle="Crie sua área do cliente para salvar, acompanhar e conferir suas apostas."
            >
                <form onSubmit={submit} className="space-y-5">
                    <div>
                        <label
                            htmlFor="name"
                            className="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            Nome
                        </label>
                        <input
                            id="name"
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            autoComplete="name"
                            required
                            className={`w-full rounded-2xl border px-4 py-3 outline-none transition ${
                                errors.name
                                    ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100'
                                    : 'border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100'
                            }`}
                            placeholder="Digite seu nome"
                        />
                        {errors.name ? (
                            <div className="mt-1.5 text-sm text-red-600">{errors.name}</div>
                        ) : null}
                    </div>

                    <div>
                        <label
                            htmlFor="email"
                            className="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            E-mail
                        </label>
                        <input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            autoComplete="email"
                            required
                            className={`w-full rounded-2xl border px-4 py-3 outline-none transition ${
                                errors.email
                                    ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100'
                                    : 'border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100'
                            }`}
                            placeholder="Digite seu e-mail"
                        />
                        {errors.email ? (
                            <div className="mt-1.5 text-sm text-red-600">{errors.email}</div>
                        ) : null}
                    </div>

                    <div>
                        <label
                            htmlFor="password"
                            className="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            Senha
                        </label>
                        <input
                            id="password"
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            autoComplete="new-password"
                            required
                            className={`w-full rounded-2xl border px-4 py-3 outline-none transition ${
                                errors.password
                                    ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100'
                                    : 'border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100'
                            }`}
                            placeholder="Crie uma senha"
                        />
                        {errors.password ? (
                            <div className="mt-1.5 text-sm text-red-600">{errors.password}</div>
                        ) : null}
                    </div>

                    <div>
                        <label
                            htmlFor="password_confirmation"
                            className="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            Confirmar senha
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            autoComplete="new-password"
                            required
                            className={`w-full rounded-2xl border px-4 py-3 outline-none transition ${
                                errors.password_confirmation
                                    ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100'
                                    : 'border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100'
                            }`}
                            placeholder="Repita sua senha"
                        />
                        {errors.password_confirmation ? (
                            <div className="mt-1.5 text-sm text-red-600">
                                {errors.password_confirmation}
                            </div>
                        ) : null}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-2xl bg-blue-700 px-4 py-3 font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        {processing ? 'Criando conta...' : 'Criar conta'}
                    </button>
                </form>

                <div className="mt-6 text-center text-sm text-slate-600">
                    Já tem conta?{' '}
                    <Link href="/login" className="font-semibold text-blue-700 hover:underline">
                        Entrar
                    </Link>
                </div>
            </AuthShell>
        </>
    );
}
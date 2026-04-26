import { Head, Link, useForm } from '@inertiajs/react';
import LotteryLogo from '../Lottery/components/LotteryLogo';

export default function Login({ status, canResetPassword = false }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Entrar" />

            <div
                className="min-h-screen"
                style={{
                    background:
                        'radial-gradient(circle at top left, rgba(79, 70, 229, 0.08), transparent 28%), radial-gradient(circle at top right, rgba(12, 90, 150, 0.12), transparent 24%), linear-gradient(180deg, #f4f8fc 0%, #edf3f8 46%, #f7fbff 100%)',
                }}
            >
                <div className="mx-auto flex min-h-screen max-w-7xl items-center justify-center px-4 py-8 md:px-8">
                    <div className="grid w-full max-w-6xl overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_30px_80px_rgba(8,59,99,0.10)] lg:grid-cols-[1.08fr_0.92fr]">
                        <div
                            className="relative hidden overflow-hidden p-10 text-white lg:flex lg:flex-col lg:justify-between"
                            style={{
                                background:
                                    'linear-gradient(135deg, rgba(8,59,99,0.98) 0%, rgba(12,90,150,0.96) 45%, rgba(108,99,255,0.84) 100%)',
                            }}
                        >
                            <div className="absolute -left-10 top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl" />
                            <div className="absolute bottom-0 right-0 h-56 w-56 rounded-full bg-[#9b8cff]/25 blur-3xl" />

                            <div className="relative z-10">
                                <div className="text-sm font-semibold uppercase tracking-[0.28em] text-white/70">
                                    Área do cliente
                                </div>

                                <h1 className="mt-5 text-5xl font-black leading-none tracking-tight">
                                    <LotteryLogo size="lg" />
                                    <span className="block">Loterias Inteligente</span>
                                </h1>

                                <p className="mt-6 max-w-xl text-lg leading-8 text-white/80">
                                    Entre para salvar apostas, acompanhar seu histórico privado e usar
                                    recursos inteligentes com uma experiência mais organizada e bonita.
                                </p>
                            </div>

                            <div className="relative z-10 mt-10 space-y-4">
                                <div className="rounded-[24px] border border-white/15 bg-white/10 p-5 backdrop-blur-sm">
                                    <div className="text-sm font-semibold uppercase tracking-[0.18em] text-white/70">
                                        O que você encontra aqui
                                    </div>

                                    <ul className="mt-4 space-y-3 text-sm leading-7 text-white/85">
                                        <li>• Histórico privado das suas combinações</li>
                                        <li>• Registro e conferência de apostas</li>
                                        <li>• Área pessoal com últimas apostas</li>
                                        <li>• Ferramentas inteligentes para análise</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div className="p-6 md:p-10 lg:p-12">
                            <Link
                                href="/"
                                className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                            >
                                ← Voltar
                            </Link>

                            <div className="mt-8 max-w-xl">
                                <div className="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">
                                    Entrar
                                </div>

                                <h2 className="mt-3 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">
                                    Acesse sua conta
                                </h2>

                                <p className="mt-3 text-base leading-7 text-slate-500">
                                    Faça login para salvar apostas, ver seu histórico privado e usar as
                                    análises inteligentes.
                                </p>
                            </div>

                            {status ? (
                                <div className="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                    {status}
                                </div>
                            ) : null}

                            <form onSubmit={submit} className="mt-8 space-y-6">
                                <div>
                                    <label className="mb-2 block text-sm font-semibold text-slate-700">
                                        E-mail
                                    </label>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        className="block w-full rounded-[20px] border border-slate-200 bg-white px-4 py-4 text-base text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                        autoComplete="username"
                                    />
                                    {errors.email ? (
                                        <div className="mt-2 text-sm font-medium text-rose-600">
                                            {errors.email}
                                        </div>
                                    ) : null}
                                </div>

                                <div>
                                    <div className="mb-2 flex items-center justify-between gap-3">
                                        <label className="block text-sm font-semibold text-slate-700">
                                            Senha
                                        </label>

                                        {canResetPassword ? (
                                            <Link
                                                href="/password.request"
                                                className="text-sm font-semibold text-[#0c5a96] hover:underline"
                                            >
                                                Esqueci minha senha
                                            </Link>
                                        ) : null}
                                    </div>

                                    <input
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        className="block w-full rounded-[20px] border border-slate-200 bg-white px-4 py-4 text-base text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                        autoComplete="current-password"
                                    />
                                    {errors.password ? (
                                        <div className="mt-2 text-sm font-medium text-rose-600">
                                            {errors.password}
                                        </div>
                                    ) : null}
                                </div>

                                <label className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <input
                                        type="checkbox"
                                        checked={data.remember}
                                        onChange={(e) => setData('remember', e.target.checked)}
                                        className="h-4 w-4 rounded border-slate-300 text-[#0c5a96] focus:ring-[#0c5a96]"
                                    />
                                    <span className="text-sm font-medium text-slate-700">
                                        Lembrar de mim
                                    </span>
                                </label>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex w-full items-center justify-center rounded-[20px] px-5 py-4 text-base font-bold text-white shadow-[0_18px_30px_rgba(12,90,150,0.20)] transition disabled:opacity-70"
                                    style={{
                                        background:
                                            'linear-gradient(180deg, #1670b6 0%, #0c5a96 100%)',
                                    }}
                                >
                                    {processing ? 'Entrando...' : 'Entrar na conta'}
                                </button>
                            </form>

                            <div className="mt-8 border-t border-slate-200 pt-6 text-sm text-slate-500">
                                Ainda não tem conta?{' '}
                                <Link
                                    href="/register"
                                    className="font-bold text-[#0c5a96] hover:underline"
                                >
                                    Criar conta
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
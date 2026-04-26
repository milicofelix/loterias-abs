import { Link, usePage } from '@inertiajs/react';

export default function AuthShell({ title, subtitle, children, eyebrow = 'Área do cliente' }) {
    const { flash = {} } = usePage().props;

    return (
        <div
            className="min-h-screen"
            style={{
                background:
                    'radial-gradient(circle at top left, rgba(79, 70, 229, 0.08), transparent 28%), radial-gradient(circle at top right, rgba(12, 90, 150, 0.12), transparent 24%), linear-gradient(180deg, #f4f8fc 0%, #edf3f8 46%, #f7fbff 100%)',
            }}
        >
            <div className="mx-auto flex min-h-screen max-w-7xl items-center justify-center px-4 py-8 md:px-8">
                <div className="grid w-full max-w-6xl overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_30px_80px_rgba(8,59,99,0.10)] lg:grid-cols-[1.02fr_0.98fr]">
                    <div
                        className="relative hidden overflow-hidden p-10 text-white lg:flex lg:flex-col lg:justify-between"
                        style={{
                            background:
                                'linear-gradient(135deg, rgba(8,59,99,0.98) 0%, rgba(12,90,150,0.96) 45%, rgba(108,99,255,0.84) 100%)',
                        }}
                    >
                        <div className="absolute -left-10 top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl" />
                        <div className="absolute bottom-0 right-0 h-56 w-56 rounded-full bg-[#9b8cff]/25 blur-3xl" />

                        <div className="relative z-10 max-w-xl">
                            <div className="text-sm font-semibold uppercase tracking-[0.28em] text-white/70">
                                {eyebrow}
                            </div>

                            <h1 className="mt-5 text-5xl font-black leading-none tracking-tight">
                                ABS
                                <span className="block">Loterias Inteligente</span>
                            </h1>

                            <p className="mt-6 text-lg leading-8 text-white/80">
                                Experiência mais organizada, bonita e confortável para salvar apostas,
                                revisar combinações e navegar pelo histórico sem sensação de tela apertada.
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
                                    <li>• Visual moderno com mais respiro</li>
                                    <li>• Ferramentas inteligentes para análise</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div className="p-6 md:p-10 lg:p-12">
                        <Link href="/" className="btn-secondary">
                            ← Voltar
                        </Link>

                        <div className="mt-8 max-w-xl">
                            <div className="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">
                                {eyebrow}
                            </div>

                            <h2 className="mt-3 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">
                                {title}
                            </h2>

                            {subtitle ? (
                                <p className="mt-3 text-base leading-7 text-slate-500">{subtitle}</p>
                            ) : null}
                        </div>

                        {flash.error ? (
                            <div className="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                                {flash.error}
                            </div>
                        ) : null}

                        {flash.success ? (
                            <div className="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                {flash.success}
                            </div>
                        ) : null}

                        <div className="mt-8">{children}</div>
                    </div>
                </div>
            </div>
        </div>
    );
}

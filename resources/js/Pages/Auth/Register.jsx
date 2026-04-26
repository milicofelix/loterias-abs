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
                eyebrow="Cadastro"
                title="Criar conta"
                subtitle="Crie sua área do cliente para salvar, acompanhar e conferir suas apostas com uma navegação mais organizada."
            >
                <form onSubmit={submit} className="space-y-5">
                    <div>
                        <label htmlFor="name" className="field-label">
                            Nome
                        </label>
                        <input
                            id="name"
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            autoComplete="name"
                            required
                            className={`field-input ${errors.name ? 'field-input-error' : ''}`}
                            placeholder="Digite seu nome"
                        />
                        {errors.name ? <div className="error-text">{errors.name}</div> : null}
                    </div>

                    <div>
                        <label htmlFor="email" className="field-label">
                            E-mail
                        </label>
                        <input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            autoComplete="email"
                            required
                            className={`field-input ${errors.email ? 'field-input-error' : ''}`}
                            placeholder="Digite seu e-mail"
                        />
                        {errors.email ? <div className="error-text">{errors.email}</div> : null}
                    </div>

                    <div className="grid gap-5 md:grid-cols-2">
                        <div>
                            <label htmlFor="password" className="field-label">
                                Senha
                            </label>
                            <input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                autoComplete="new-password"
                                required
                                className={`field-input ${errors.password ? 'field-input-error' : ''}`}
                                placeholder="Crie uma senha"
                            />
                            {errors.password ? <div className="error-text">{errors.password}</div> : null}
                        </div>

                        <div>
                            <label htmlFor="password_confirmation" className="field-label">
                                Confirmar senha
                            </label>
                            <input
                                id="password_confirmation"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                autoComplete="new-password"
                                required
                                className={`field-input ${errors.password_confirmation ? 'field-input-error' : ''}`}
                                placeholder="Repita sua senha"
                            />
                            {errors.password_confirmation ? (
                                <div className="error-text">{errors.password_confirmation}</div>
                            ) : null}
                        </div>
                    </div>

                    <button type="submit" disabled={processing} className="btn-primary w-full">
                        {processing ? 'Criando conta...' : 'Criar conta'}
                    </button>
                </form>

                <div className="mt-6 text-center text-sm text-slate-600">
                    Já tem conta?{' '}
                    <Link href="/login" className="font-semibold text-[#0c5a96] hover:underline">
                        Entrar
                    </Link>
                </div>
            </AuthShell>
        </>
    );
}

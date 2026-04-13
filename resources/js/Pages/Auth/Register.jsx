import { Head, Link, useForm } from '@inertiajs/react';
import AuthShell from '@/Components/AuthShell';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(e) {
        e.preventDefault();
        post('/register');
    }

    return (
        <>
            <Head title="Criar conta" />
            <AuthShell title="Criar conta" subtitle="Crie sua área do cliente para salvar e conferir apostas.">
                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label className="mb-1 block text-sm font-semibold text-slate-700">Nome</label>
                        <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} className="w-full rounded-2xl border border-slate-300 px-4 py-3" />
                        {errors.name ? <div className="mt-1 text-sm text-red-600">{errors.name}</div> : null}
                    </div>
                    <div>
                        <label className="mb-1 block text-sm font-semibold text-slate-700">E-mail</label>
                        <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className="w-full rounded-2xl border border-slate-300 px-4 py-3" />
                        {errors.email ? <div className="mt-1 text-sm text-red-600">{errors.email}</div> : null}
                    </div>
                    <div>
                        <label className="mb-1 block text-sm font-semibold text-slate-700">Senha</label>
                        <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} className="w-full rounded-2xl border border-slate-300 px-4 py-3" />
                        {errors.password ? <div className="mt-1 text-sm text-red-600">{errors.password}</div> : null}
                    </div>
                    <div>
                        <label className="mb-1 block text-sm font-semibold text-slate-700">Confirmar senha</label>
                        <input type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} className="w-full rounded-2xl border border-slate-300 px-4 py-3" />
                    </div>
                    <button type="submit" disabled={processing} className="w-full rounded-2xl bg-blue-700 px-4 py-3 font-semibold text-white">
                        {processing ? 'Criando...' : 'Criar conta'}
                    </button>
                </form>
                <div className="mt-6 text-sm text-slate-600">
                    Já tem conta? <Link href="/login" className="font-semibold text-blue-700">Entrar</Link>
                </div>
            </AuthShell>
        </>
    );
}

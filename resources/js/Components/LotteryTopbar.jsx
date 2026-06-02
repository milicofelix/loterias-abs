import { Link, router, usePage } from '@inertiajs/react';
import LotteryLogo from './LotteryLogo';

export default function LotteryTopbar() {
    const { auth } = usePage().props;

    function logout() {
        router.post('/logout');
    }

    return (
        <header className="bg-white border-b border-slate-200 shadow-sm">
            <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <Link href="/lottery/modalities" className="flex items-center gap-3">
                    <LotteryLogo size="md" />                      
                    <div>
                        <div className="font-extrabold text-slate-800 leading-tight">
                            Loterias ABS
                        </div>
                        <div className="text-xs text-slate-500">
                            Inteligência para jogos
                        </div>
                    </div>
                </Link>

                <div className="flex items-center gap-4">
                    <div className="text-right hidden sm:block">
                        <div className="text-sm font-semibold text-slate-700">
                            {auth?.user?.name ?? 'Usuário'}
                        </div>
                        <div className="text-xs text-slate-500">
                            {auth?.user?.email ?? ''}
                        </div>
                    </div>

                    <button
                        type="button"
                        onClick={logout}
                        className="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-700 transition"
                    >
                        Sair
                    </button>
                </div>
            </div>
        </header>
    );
}
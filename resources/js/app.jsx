import '../css/app.css';
import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { useEffect, useState } from 'react';
import AppPreloader from '@/Components/AppPreloader';

function AppShell({ App, props }) {
    const [loading, setLoading] = useState(false);
    const [finishSignal, setFinishSignal] = useState(0);
    const [loadingText, setLoadingText] = useState({
        title: 'Carregando...',
        description: 'Aguarde enquanto processamos sua solicitação.',
    });

    useEffect(() => {
        const removeStart = router.on('start', () => {
            setLoadingText({
                title: 'Carregando conteúdo...',
                description: 'Estamos preparando a próxima tela para você.',
            });
            setLoading(true);
        });

        const removeFinish = router.on('finish', () => {
            setLoading(false);
            setFinishSignal(Date.now());
        });

        const removeError = router.on('error', () => {
            setLoading(false);
            setFinishSignal(Date.now());
        });

        const removeInvalid = router.on('invalid', () => {
            setLoading(false);
            setFinishSignal(Date.now());
        });

        return () => {
            removeStart?.();
            removeFinish?.();
            removeError?.();
            removeInvalid?.();
        };
    }, []);

    return (
        <>
            <AppPreloader
                visible={loading}
                finishSignal={finishSignal}
                title={loadingText.title}
                description={loadingText.description}
            />
            <App {...props} />
        </>
    );
}

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx')
        ),
    setup({ el, App, props }) {
        createRoot(el).render(<AppShell App={App} props={props} />);
    },
    progress: false,
});
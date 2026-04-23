import { useEffect, useMemo, useRef, useState } from 'react';

export default function AppPreloader({
    visible = false,
    title = 'Processando...',
    description = 'Aguarde enquanto finalizamos sua solicitação.',
    finishSignal = 0,
}) {
    const [progress, setProgress] = useState(0);
    const [mounted, setMounted] = useState(false);
    const intervalRef = useRef(null);
    const finishTimeoutRef = useRef(null);

    useEffect(() => {
        if (visible) {
            setMounted(true);
            setProgress(0);

            if (finishTimeoutRef.current) clearTimeout(finishTimeoutRef.current);
            if (intervalRef.current) clearInterval(intervalRef.current);

            intervalRef.current = setInterval(() => {
                setProgress((old) => {
                    if (old >= 94) return old;

                    let increment = 1;
                    if (old < 18) increment = 9;
                    else if (old < 38) increment = 6;
                    else if (old < 60) increment = 4;
                    else if (old < 80) increment = 2;
                    else increment = 1;

                    return Math.min(old + increment, 94);
                });
            }, 180);
        } else if (mounted) {
            if (intervalRef.current) clearInterval(intervalRef.current);

            setProgress(100);

            finishTimeoutRef.current = setTimeout(() => {
                setMounted(false);
                setProgress(0);
            }, 320);
        }

        return () => {
            if (intervalRef.current) clearInterval(intervalRef.current);
        };
    }, [visible, mounted]);

    useEffect(() => {
        if (!mounted) return;

        if (finishSignal > 0 && !visible) {
            setProgress(100);

            if (finishTimeoutRef.current) clearTimeout(finishTimeoutRef.current);

            finishTimeoutRef.current = setTimeout(() => {
                setMounted(false);
                setProgress(0);
            }, 320);
        }
    }, [finishSignal, visible, mounted]);

    useEffect(() => {
        return () => {
            if (intervalRef.current) clearInterval(intervalRef.current);
            if (finishTimeoutRef.current) clearTimeout(finishTimeoutRef.current);
        };
    }, []);

    const percentText = useMemo(() => `${Math.round(progress)}%`, [progress]);

    if (!mounted) return null;

    return (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/88 backdrop-blur-md">
            <div className="mx-4 w-full max-w-lg overflow-hidden rounded-[28px] border border-white/10 bg-white shadow-[0_30px_90px_rgba(0,0,0,0.35)]">
                <div className="h-1.5 w-full bg-slate-200">
                    <div
                        className="h-full bg-sky-600 transition-all duration-300 ease-out"
                        style={{ width: `${progress}%` }}
                    />
                </div>

                <div className="px-8 py-9">
                    <div className="flex flex-col items-center text-center">
                        <div className="relative mb-6">
                            <div className="h-20 w-20 rounded-full border-4 border-slate-200 border-t-sky-600 animate-spin" />
                            <div className="absolute inset-0 flex items-center justify-center text-sm font-extrabold text-slate-700">
                                {percentText}
                            </div>
                        </div>

                        <h2 className="text-2xl font-bold text-slate-800">
                            {title}
                        </h2>

                        <p className="mt-3 max-w-md text-sm leading-6 text-slate-500">
                            {description}
                        </p>

                        <div className="mt-7 w-full">
                            <div className="h-3.5 w-full overflow-hidden rounded-full bg-slate-200">
                                <div
                                    className="h-full rounded-full bg-sky-600 transition-all duration-300 ease-out"
                                    style={{ width: `${progress}%` }}
                                />
                            </div>

                            <div className="mt-3 text-sm font-semibold text-slate-600">
                                {percentText}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
import { Link } from '@inertiajs/react';

export const lotteryPalette = {
    blue: '#0c5a96',
    blueDark: '#083b63',
    blueDeep: '#0f4c81',
    bg: '#edf3f8',
    surface: '#ffffff',
    soft: '#f7fbff',
    line: '#d8e3ee',
    muted: '#667085',
    text: '#0f1728',
    glow: 'rgba(12, 90, 150, 0.16)',
};

export function LotteryPage({ children }) {
    return (
        <div
            className="min-h-screen"
            style={{
                background:
                    'radial-gradient(circle at top left, rgba(79, 70, 229, 0.10), transparent 30%), radial-gradient(circle at top right, rgba(12, 90, 150, 0.14), transparent 24%), linear-gradient(180deg, #f4f8fc 0%, #edf3f8 46%, #f7fbff 100%)',
            }}
        >
            <div className="mx-auto max-w-7xl px-4 py-6 md:px-8 md:py-10">{children}</div>
        </div>
    );
}

export function HeroBanner({ eyebrow, title, subtitle, contest, children, art = 'stack' }) {
    const image = art === 'single' ? '/lottery-assets/quina-ticket-single.png' : '/lottery-assets/quina-ticket-stack.png';

    return (
        <section
            className="relative overflow-hidden rounded-[32px] border p-6 shadow-[0_24px_60px_rgba(10,44,73,0.10)] md:p-10"
            style={{
                borderColor: lotteryPalette.line,
                background:
                    'linear-gradient(135deg, rgba(8,59,99,0.97) 0%, rgba(12,90,150,0.95) 42%, rgba(108,99,255,0.82) 100%)',
            }}
        >
            <div className="absolute inset-y-0 right-0 hidden w-[40%] items-center justify-center lg:flex">
                <div className="absolute inset-0 bg-gradient-to-l from-white/5 to-transparent" />
                <img
                    src={image}
                    alt="Bilhete ilustrativo da Quina"
                    className="relative max-h-[360px] translate-x-6 drop-shadow-[0_30px_40px_rgba(3,13,31,0.45)]"
                />
            </div>

            <div className="absolute -left-10 top-8 h-32 w-32 rounded-full bg-white/10 blur-2xl" />
            <div className="absolute bottom-0 left-1/3 h-40 w-40 rounded-full bg-[#9b8cff]/20 blur-3xl" />

            <div className="relative z-10 max-w-3xl text-white">
                {eyebrow ? (
                    <div className="text-sm font-semibold uppercase tracking-[0.32em] text-white/75 md:text-base">
                        {eyebrow}
                    </div>
                ) : null}

                <div className="mt-3 flex flex-wrap items-end gap-3 md:gap-5">
                    <h1 className="text-4xl font-black leading-none tracking-tight md:text-6xl">{title}</h1>
                    {contest ? (
                        <div className="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-lg font-semibold text-white/85 backdrop-blur-sm md:text-2xl">
                            {contest}
                        </div>
                    ) : null}
                </div>

                {subtitle ? (
                    <p className="mt-5 max-w-2xl text-base leading-7 text-white/80 md:text-2xl md:leading-9">
                        {subtitle}
                    </p>
                ) : null}

                {children ? <div className="mt-7 flex flex-wrap gap-3">{children}</div> : null}
            </div>
        </section>
    );
}

export function ActionLink({ href, children, variant = 'primary', className = '' }) {
    const base = 'inline-flex min-h-[52px] items-center justify-center rounded-2xl px-5 py-3 text-sm font-semibold transition md:px-6 md:text-base';

    const styles =
        variant === 'secondary'
            ? {
                  backgroundColor: 'rgba(255,255,255,0.12)',
                  color: '#fff',
                  border: '1px solid rgba(255,255,255,0.18)',
                  backdropFilter: 'blur(10px)',
              }
            : {
                  backgroundColor: '#fff',
                  color: lotteryPalette.blue,
                  boxShadow: '0 14px 28px rgba(8,59,99,0.20)',
              };

    return (
        <Link href={href} className={`${base} ${className}`.trim()} style={styles}>
            {children}
        </Link>
    );
}

export function SurfaceCard({ children, className = '', padded = true }) {
    return (
        <section
            className={`rounded-[28px] border bg-white shadow-[0_18px_45px_rgba(15,76,129,0.07)] ${padded ? 'p-5 md:p-7' : ''} ${className}`.trim()}
            style={{ borderColor: lotteryPalette.line }}
        >
            {children}
        </section>
    );
}

export function SectionHeading({ eyebrow, title, description, aside = null }) {
    return (
        <div className="mb-5 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                {eyebrow ? (
                    <div className="text-xs font-bold uppercase tracking-[0.24em]" style={{ color: lotteryPalette.muted }}>
                        {eyebrow}
                    </div>
                ) : null}
                <h2 className="mt-2 text-2xl font-extrabold tracking-tight md:text-3xl" style={{ color: lotteryPalette.blueDark }}>
                    {title}
                </h2>
                {description ? (
                    <p className="mt-2 max-w-2xl text-sm leading-6 md:text-base" style={{ color: lotteryPalette.muted }}>
                        {description}
                    </p>
                ) : null}
            </div>
            {aside}
        </div>
    );
}

export function MetricCard({ label, value, helper }) {
    return (
        <div
            className="rounded-[24px] border p-5 md:p-6"
            style={{
                borderColor: lotteryPalette.line,
                background: 'linear-gradient(180deg, #ffffff 0%, #f8fbff 100%)',
            }}
        >
            <div className="text-sm font-semibold" style={{ color: lotteryPalette.muted }}>
                {label}
            </div>
            <div className="mt-3 text-4xl font-black tracking-tight md:text-5xl" style={{ color: lotteryPalette.blueDark }}>
                {value}
            </div>
            {helper ? (
                <div className="mt-2 text-sm" style={{ color: lotteryPalette.muted }}>
                    {helper}
                </div>
            ) : null}
        </div>
    );
}

export function NumberBall({ number, active = true, size = 'md', subtle = false }) {
    const sizeMap = {
        sm: { box: 44, font: 16 },
        md: { box: 56, font: 22 },
        lg: { box: 64, font: 26 },
    };

    const current = sizeMap[size] || sizeMap.md;

    return (
        <span
            style={{
                display: 'inline-flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: `${current.box}px`,
                height: `${current.box}px`,
                minWidth: `${current.box}px`,
                minHeight: `${current.box}px`,
                maxWidth: `${current.box}px`,
                maxHeight: `${current.box}px`,
                borderRadius: '9999px',
                flex: '0 0 auto',
                boxSizing: 'border-box',
                fontWeight: 800,
                fontSize: `${current.font}px`,
                lineHeight: 1,
                letterSpacing: '0.02em',
                verticalAlign: 'middle',
                background: subtle
                    ? '#eef5fb'
                    : active
                        ? 'linear-gradient(180deg, #1670b6 0%, #0c5a96 100%)'
                        : '#ffffff',
                color: subtle
                    ? lotteryPalette.blue
                    : active
                        ? '#ffffff'
                        : lotteryPalette.blueDark,
                border: subtle || !active
                    ? `1px solid ${lotteryPalette.line}`
                    : 'none',
                boxShadow: active && !subtle
                    ? '0 14px 24px rgba(12, 90, 150, 0.22)'
                    : 'none',
            }}
        >
            {String(number).padStart(2, '0')}
        </span>
    );
}

export function Tag({ children, active = false, onClick, type = 'button' }) {
    return (
        <button
            type={type}
            onClick={onClick}
            className="min-h-[44px] rounded-2xl px-4 text-sm font-semibold md:text-base"
            style={{
                background: active ? 'linear-gradient(180deg, #1670b6 0%, #0c5a96 100%)' : '#fff',
                color: active ? '#fff' : lotteryPalette.blue,
                border: `1px solid ${active ? '#0c5a96' : lotteryPalette.line}`,
                boxShadow: active ? '0 10px 20px rgba(12, 90, 150, 0.18)' : 'none',
            }}
        >
            {children}
        </button>
    );
}

export function EmptyState({ title, description }) {
    return (
        <div
            className="rounded-[28px] border px-6 py-10 text-center"
            style={{ borderColor: lotteryPalette.line, backgroundColor: '#fff' }}
        >
            <div className="text-xl font-bold" style={{ color: lotteryPalette.blueDark }}>{title}</div>
            <p className="mx-auto mt-3 max-w-xl text-sm leading-6 md:text-base" style={{ color: lotteryPalette.muted }}>
                {description}
            </p>
        </div>
    );
}

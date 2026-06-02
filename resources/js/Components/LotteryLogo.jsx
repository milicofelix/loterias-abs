export default function LotteryLogo({ className = '', size = 'md' }) {
    const sizes = {
        sm: 'h-10',
        md: 'h-16',
        lg: 'h-40',
    };

    return (
        <img
            src="/lottery-assets/abs-logo.png"
            alt="Loterias ABS"
            className={`${sizes[size] ?? sizes.md} w-auto object-contain ${className}`}
        />
    );
}
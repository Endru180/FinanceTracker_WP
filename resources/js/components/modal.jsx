import { useEffect } from "react";

export default function Modal({
    title,
    children,
    onClose,
    maxWidth = "max-w-lg",
}) {
    useEffect(() => {
        if (!onClose) return undefined;
        function onKeyDown(e) {
            if (e.key === "Escape") onClose();
        }
        window.addEventListener("keydown", onKeyDown);
        return () => window.removeEventListener("keydown", onKeyDown);
    }, [onClose]);

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
            onClick={onClose}
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-label={title}
                onClick={(e) => e.stopPropagation()}
                className={`max-h-[90vh] w-full ${maxWidth} overflow-y-auto rounded-md border border-border bg-surface p-8`}
            >
                <h2 className="mb-6 font-display text-xl font-semibold text-accent">
                    {title}
                </h2>
                {children}
            </div>
        </div>
    );
}

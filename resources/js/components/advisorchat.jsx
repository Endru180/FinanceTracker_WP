import { useEffect, useRef, useState } from "react";

export default function AdvisorChat() {
    const [messages, setMessages] = useState([]);
    const [input, setInput] = useState("");
    const listRef = useRef(null);

    // Keep the newest message in view
    useEffect(() => {
        if (listRef.current)
            listRef.current.scrollTop = listRef.current.scrollHeight;
    }, [messages]);

    function handleSend(e) {
        e.preventDefault();
        const text = input.trim();
        if (!text) return;
        setMessages((prev) => [...prev, { id: Date.now(), text }]);
        setInput("");
    }

    return (
        <section
            aria-label="Advisor chat"
            className="flex h-full flex-col rounded-md border border-border bg-surface"
        >
            <header className="border-b border-border px-5 py-4">
                <h2 className="font-display text-base font-semibold text-accent">
                    Advisor
                </h2>
                <p className="mt-0.5 text-xs text-muted">
                    Ask about your spending.
                </p>
            </header>

            <div
                ref={listRef}
                aria-live="polite"
                className="flex-1 space-y-3 overflow-y-auto px-5 py-5"
            >
                {messages.length === 0 && (
                    <p className="text-sm text-muted">
                        This is a placeholder. Your messages show up here, but
                        there is no reply yet.
                    </p>
                )}
                {messages.map((m) => (
                    <div key={m.id} className="flex justify-end">
                        <div className="max-w-[85%] rounded-md border border-border bg-surface-raised px-3 py-2 text-sm text-ink">
                            {m.text}
                        </div>
                    </div>
                ))}
            </div>

            <form
                onSubmit={handleSend}
                className="flex gap-2 border-t border-border p-4"
            >
                <input
                    type="text"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    placeholder="Ask the advisor..."
                    aria-label="Message the advisor"
                    className="min-w-0 flex-1 rounded-md border border-border bg-bg px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none"
                />
                <button
                    type="submit"
                    className="shrink-0 rounded-md bg-accent px-4 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90"
                >
                    Send
                </button>
            </form>
        </section>
    );
}

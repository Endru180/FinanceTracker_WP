import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useFinance } from "../context/financecontext";
import Modal from "./modal";

const inputClass =
    "w-full rounded-md border border-border bg-bg px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none";
const labelClass = "mb-1.5 block text-sm font-medium text-muted";

function AuthDialog() {
    const { closeAuth, signUp, logIn, logInWithGoogle } = useFinance();
    const navigate = useNavigate();
    const [mode, setMode] = useState("login"); // 'login' or 'signup'
    const [form, setForm] = useState({ name: "", email: "", password: "" });
    const isSignup = mode === "signup";

    function update(field, value) {
        setForm((prev) => ({ ...prev, [field]: value }));
    }

    function finish() {
        closeAuth();
        navigate("/profile");
    }

    function handleSubmit(e) {
        e.preventDefault();
        // Demo only: nothing is checked or stored. Replace with a call to the Laravel backend.
        if (isSignup) signUp({ name: form.name, email: form.email });
        else logIn({ email: form.email });
        finish();
    }

    function handleGoogle() {
        // Demo only: no real Google account is used.
        logInWithGoogle();
        finish();
    }

    return (
        <Modal
            title="Log in to continue"
            maxWidth="max-w-md"
            onClose={closeAuth}
        >
            <p className="-mt-3 mb-5 text-sm text-muted">
                Log in to view your profile and manage your payment accounts.
            </p>

            <div className="mb-5 flex gap-2">
                {[
                    ["login", "Log in"],
                    ["signup", "Create account"],
                ].map(([key, label]) => (
                    <button
                        key={key}
                        type="button"
                        aria-pressed={mode === key}
                        onClick={() => setMode(key)}
                        className={`rounded-md px-4 py-2 text-sm font-medium transition-colors ${
                            mode === key
                                ? "bg-surface-raised text-accent"
                                : "text-muted hover:text-ink"
                        }`}
                    >
                        {label}
                    </button>
                ))}
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
                {isSignup && (
                    <div>
                        <label htmlFor="auth-name" className={labelClass}>
                            Name
                        </label>
                        <input
                            id="auth-name"
                            type="text"
                            value={form.name}
                            onChange={(e) => update("name", e.target.value)}
                            className={inputClass}
                            autoComplete="name"
                            required
                        />
                    </div>
                )}
                <div>
                    <label htmlFor="auth-email" className={labelClass}>
                        Email
                    </label>
                    <input
                        id="auth-email"
                        type="email"
                        value={form.email}
                        onChange={(e) => update("email", e.target.value)}
                        className={inputClass}
                        autoComplete="email"
                        required
                    />
                </div>
                <div>
                    <label htmlFor="auth-password" className={labelClass}>
                        Password
                    </label>
                    <input
                        id="auth-password"
                        type="password"
                        value={form.password}
                        onChange={(e) => update("password", e.target.value)}
                        className={inputClass}
                        minLength={isSignup ? 8 : undefined}
                        autoComplete={
                            isSignup ? "new-password" : "current-password"
                        }
                        required
                    />
                    {isSignup && (
                        <p className="mt-1.5 text-xs text-muted">
                            At least 8 characters.
                        </p>
                    )}
                </div>

                <button
                    type="submit"
                    className="w-full rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90"
                >
                    {isSignup ? "Create account" : "Log in"}
                </button>
            </form>

            <div className="my-5 flex items-center gap-3 text-xs text-muted">
                <span className="h-px flex-1 bg-border" />
                or
                <span className="h-px flex-1 bg-border" />
            </div>

            <button
                type="button"
                onClick={handleGoogle}
                className="w-full rounded-md border border-border px-5 py-2.5 text-sm font-medium text-ink transition-colors hover:bg-surface-raised"
            >
                Continue with Google
            </button>

            <p className="mt-5 text-xs text-muted">
                Demo only. No real account is checked or created.
            </p>
        </Modal>
    );
}

export default function AuthModal() {
    const { authOpen } = useFinance();
    return authOpen ? <AuthDialog /> : null;
}

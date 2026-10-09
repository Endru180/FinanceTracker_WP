import { Link, useLocation } from "react-router-dom";
import { useFinance } from "../context/FinanceContext";
import ThemeToggle from "./ThemeToggle";

const navItems = [
    { label: "Dashboard", icon: "⊞", path: "/" },
    { label: "Transactions", icon: "≡", path: "/transactions" },
    { label: "Goals", icon: "◆", path: "/goals" },
];

export default function Sidebar() {
    const { pathname } = useLocation();
    const { user, openAuth } = useFinance();
    const profileActive = pathname === "/profile";

    const profileContent = (
        <>
            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-border font-display text-sm text-accent">
                {user.name.charAt(0).toUpperCase()}
            </span>
            <span className="min-w-0">
                <span
                    className={`block truncate text-sm ${profileActive ? "text-accent" : "text-ink"}`}
                >
                    {user.name}
                </span>
                <span className="block truncate text-xs text-muted">
                    {user.signedIn ? user.email : "Not logged in"}
                </span>
            </span>
        </>
    );

    const profileClass = `flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left transition-colors hover:bg-surface-raised ${
        profileActive ? "bg-surface-raised" : ""
    }`;

    return (
        <aside className="fixed left-0 top-0 flex h-screen w-60 flex-col border-r border-border bg-surface">
            <div className="px-6 py-6">
                <span className="font-display text-lg font-semibold text-ink">
                    Finance<span className="text-accent">.</span>
                </span>
            </div>

            <nav className="flex-1 px-3">
                {navItems.map((item) => {
                    const active = pathname === item.path;
                    return (
                        <Link
                            key={item.label}
                            to={item.path}
                            className={`mb-1 flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors ${
                                active
                                    ? "bg-surface-raised text-accent"
                                    : "text-muted hover:bg-surface-raised hover:text-ink"
                            }`}
                        >
                            <span className="text-base">{item.icon}</span>
                            {item.label}
                        </Link>
                    );
                })}
            </nav>

            <div className="border-t border-border p-3">
                <ThemeToggle />
                {user.signedIn ? (
                    <Link
                        to="/profile"
                        aria-current={profileActive ? "page" : undefined}
                        className={profileClass}
                    >
                        {profileContent}
                    </Link>
                ) : (
                    <button
                        type="button"
                        onClick={openAuth}
                        aria-haspopup="dialog"
                        className={profileClass}
                    >
                        {profileContent}
                    </button>
                )}
            </div>
        </aside>
    );
}

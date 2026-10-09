import { useState } from "react";
import { applyTheme, getStoredTheme } from "../utils/theme";

export default function ThemeToggle() {
    const [theme, setTheme] = useState(getStoredTheme);
    const goingLight = theme === "dark";

    function toggle() {
        const next = goingLight ? "light" : "dark";
        setTheme(next);
        applyTheme(next);
    }

    return (
        <button
            type="button"
            onClick={toggle}
            className="mb-1 flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-muted transition-colors hover:bg-surface-raised hover:text-ink"
        >
            <span className="text-base" aria-hidden="true">
                {goingLight ? "☀" : "☾"}
            </span>
            {goingLight ? "Light mode" : "Dark mode"}
        </button>
    );
}

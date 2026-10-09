const STORAGE_KEY = "finance-theme";

export function getStoredTheme() {
    try {
        return localStorage.getItem(STORAGE_KEY) === "light" ? "light" : "dark";
    } catch {
        return "dark";
    }
}

export function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch {
        // Storage can be blocked. The theme still applies for this visit.
    }
}

// For SVG colors, which can't use Tailwind classes reliably
export function cssColor(name) {
    return `rgb(var(--${name}))`;
}

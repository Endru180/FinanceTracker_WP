export function formatRupiah(amount) {
    return `${amount < 0 ? "-" : ""}Rp ${Math.abs(amount).toLocaleString("id-ID")}`;
}

function pad(n) {
    return String(n).padStart(2, "0");
}

export function toDateString(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

export function todayString() {
    return toDateString(new Date());
}

export function currentMonthKey() {
    return todayString().slice(0, 7);
}

export function formatDate(dateString) {
    if (!dateString) return "";
    const [y, m, d] = dateString.split("-").map(Number);
    return new Date(y, m - 1, d).toLocaleDateString("en-GB", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
}

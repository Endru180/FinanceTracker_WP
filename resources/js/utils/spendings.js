import { currentMonthKey, todayString } from "./format";

export const DEFAULT_REMINDERS = {
    half: false,
    threeQuarters: false,
    ninetyPercent: false,
    customEnabled: false,
    customPercent: "",
};

export function sumAmount(list) {
    return list.reduce((sum, t) => sum + t.amount, 0);
}

export function monthTransactions(transactions) {
    const key = currentMonthKey();
    return transactions.filter((t) => (t.date || "").startsWith(key));
}

export function todayTransactions(transactions) {
    const today = todayString();
    return transactions.filter((t) => t.date === today);
}

// Returns [{ category, total }] sorted from biggest to smallest
export function totalsByCategory(list) {
    const totals = {};
    list.forEach((t) => {
        totals[t.category] = (totals[t.category] || 0) + t.amount;
    });
    return Object.entries(totals)
        .map(([category, total]) => ({ category, total }))
        .sort((a, b) => b.total - a.total);
}

// Turns the reminder checkboxes into a list of percentages, e.g. [50, 90]
export function activeThresholds(reminders) {
    const list = [];
    if (reminders.half) list.push(50);
    if (reminders.threeQuarters) list.push(75);
    if (reminders.ninetyPercent) list.push(90);
    const custom = Number(reminders.customPercent);
    if (reminders.customEnabled && custom >= 1 && custom <= 100)
        list.push(custom);
    return list;
}

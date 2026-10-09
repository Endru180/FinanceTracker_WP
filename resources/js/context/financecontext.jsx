import { createContext, useContext, useMemo, useState } from "react";
import { currentMonthKey, toDateString } from "../utils/format";
import { DEFAULT_REMINDERS, activeThresholds } from "../utils/spendings";

const FinanceContext = createContext(null);

export const DEFAULT_CATEGORIES = [
    "Food",
    "Fashion",
    "Entertainment",
    "Transport",
    "Health",
    "Other",
];
export const ACCOUNT_TYPES = ["Cash", "Bank", "E-wallet"];

// Placeholder profile. method is null (signed out), 'email', or 'google'.
const INITIAL_PROFILE = { name: "Alex", email: "", method: null };

const INITIAL_ACCOUNTS = [
    { id: 1, name: "Cash", type: "Cash", startingBalance: 900000 },
    { id: 2, name: "Bank BCA", type: "Bank", startingBalance: 3000000 },
    { id: 3, name: "GoPay", type: "E-wallet", startingBalance: 500000 },
];

// Sample dates always land in the current month
function dateThisMonth(daysAgo) {
    const d = new Date();
    d.setDate(Math.max(1, d.getDate() - daysAgo));
    return toDateString(d);
}

const SEED_TRANSACTIONS = [
    {
        id: 1,
        name: "Groceries",
        description: "Weekly shopping at the supermarket",
        category: "Food",
        amount: 120000,
        accountId: 2,
        date: dateThisMonth(1),
    },
    {
        id: 2,
        name: "Coffee",
        description: "",
        category: "Food",
        amount: 25000,
        accountId: 1,
        date: dateThisMonth(0),
    },
    {
        id: 3,
        name: "Movie ticket",
        description: "Weekend movie with friends",
        category: "Entertainment",
        amount: 60000,
        accountId: 3,
        date: dateThisMonth(0),
    },
    {
        id: 4,
        name: "Ojek online",
        description: "",
        category: "Transport",
        amount: 35000,
        accountId: 3,
        date: dateThisMonth(0),
    },
    {
        id: 5,
        name: "New sneakers",
        description: "Running shoes, on sale",
        category: "Fashion",
        amount: 450000,
        accountId: 2,
        date: dateThisMonth(6),
    },
    {
        id: 6,
        name: "Lunch at Warteg",
        description: "",
        category: "Food",
        amount: 30000,
        accountId: 1,
        date: dateThisMonth(0),
    },
    {
        id: 7,
        name: "Vitamins",
        description: "Monthly supply",
        category: "Health",
        amount: 20000,
        accountId: 1,
        date: dateThisMonth(0),
    },
];

export function FinanceProvider({ children }) {
    const [profile, setProfile] = useState(INITIAL_PROFILE);
    const [authOpen, setAuthOpen] = useState(false);
    const [baseAccounts, setBaseAccounts] = useState(INITIAL_ACCOUNTS);
    const [categories, setCategories] = useState(DEFAULT_CATEGORIES);
    const [rawTransactions, setRawTransactions] = useState(SEED_TRANSACTIONS);
    const [categoryGoals, setCategoryGoals] = useState({}); // { Food: { amount, reminders } }
    const [overallGoal, setOverallGoal] = useState(0);
    const [reminders, setReminders] = useState([]); // reminder popups waiting to be shown

    const user = {
        name: profile.name,
        email: profile.email,
        method: profile.method,
        signedIn: profile.method !== null,
    };

    // Newest first
    const transactions = useMemo(
        () =>
            [...rawTransactions].sort(
                (a, b) =>
                    (b.date || "").localeCompare(a.date || "") || b.id - a.id,
            ),
        [rawTransactions],
    );

    // Balance = starting balance minus everything spent from that account.
    // Because it is calculated, deleting a transaction automatically gives the money back.
    const accounts = useMemo(
        () =>
            baseAccounts.map((account) => {
                const spent = rawTransactions
                    .filter((t) => t.accountId === account.id)
                    .reduce((sum, t) => sum + t.amount, 0);
                return { ...account, balance: account.startingBalance - spent };
            }),
        [baseAccounts, rawTransactions],
    );

    const totalBalance = accounts.reduce((sum, a) => sum + a.balance, 0);

    // ---- Profile and login ----
    // The sign-in functions are placeholders. Replace their bodies with Laravel API calls later.
    function openAuth() {
        setAuthOpen(true);
    }

    function closeAuth() {
        setAuthOpen(false);
    }

    function updateName(name) {
        setProfile((prev) => ({ ...prev, name: name.trim() }));
    }

    function signUp({ name, email }) {
        setProfile((prev) => ({
            name: name.trim() || prev.name,
            email: email.trim(),
            method: "email",
        }));
    }

    function logIn({ email }) {
        setProfile((prev) => ({
            ...prev,
            email: email.trim(),
            method: "email",
        }));
    }

    // Simulation only: no real Google account is used.
    function logInWithGoogle() {
        setProfile((prev) => ({
            ...prev,
            email: "alex@gmail.com",
            method: "google",
        }));
    }

    function logOut() {
        setProfile((prev) => ({ ...prev, email: "", method: null }));
    }

    // ---- Payment accounts ----
    function addAccount({ name, type, balance }) {
        setBaseAccounts((prev) => [
            ...prev,
            {
                id: Date.now(),
                name: name.trim(),
                type,
                startingBalance: Number(balance) || 0,
            },
        ]);
    }

    // The user edits the current balance. The starting balance is adjusted to match,
    // so transactions keep counting correctly.
    function updateAccount(id, { name, type, balance }) {
        const spent = rawTransactions
            .filter((t) => t.accountId === id)
            .reduce((sum, t) => sum + t.amount, 0);
        setBaseAccounts((prev) =>
            prev.map((a) =>
                a.id === id
                    ? {
                          ...a,
                          name: name.trim(),
                          type,
                          startingBalance: (Number(balance) || 0) + spent,
                      }
                    : a,
            ),
        );
    }

    // Returns false if the account can't be deleted
    function deleteAccount(id) {
        const hasTransactions = rawTransactions.some((t) => t.accountId === id);
        if (hasTransactions || baseAccounts.length <= 1) return false;
        setBaseAccounts((prev) => prev.filter((a) => a.id !== id));
        return true;
    }

    // ---- Categories ----
    // Returns { ok: true } or { ok: false, error: '...' }
    function addCategory(rawName) {
        const name = rawName.trim();
        if (!name) return { ok: false, error: "Enter a category name." };
        if (name.length > 30)
            return { ok: false, error: "Use 30 characters or fewer." };
        if (categories.some((c) => c.toLowerCase() === name.toLowerCase())) {
            return { ok: false, error: `"${name}" already exists.` };
        }
        setCategories((prev) => [...prev, name]);
        return { ok: true };
    }

    // Returns false if the category can't be deleted (it is in use, or it is the last one)
    function deleteCategory(name) {
        const inUse = rawTransactions.some((t) => t.category === name);
        if (inUse || categories.length <= 1) return false;
        setCategories((prev) => prev.filter((c) => c !== name));
        // The category's goal and reminders go with it
        setCategoryGoals((prev) => {
            const next = { ...prev };
            delete next[name];
            return next;
        });
        return true;
    }

    // ---- Transactions ----
    function clean({ name, description, category, amount, accountId, date }) {
        return {
            name: name.trim() || category,
            description: (description || "").trim(),
            category,
            amount: Number(amount),
            accountId: Number(accountId),
            date,
        };
    }

    // Queues a reminder if this new transaction pushes a category past one of its reminder points
    function checkReminders(newTx) {
        const goal = categoryGoals[newTx.category];
        if (!goal || goal.amount <= 0) return;

        const monthKey = currentMonthKey();
        if (!(newTx.date || "").startsWith(monthKey)) return;

        const before = rawTransactions
            .filter(
                (t) =>
                    t.category === newTx.category &&
                    (t.date || "").startsWith(monthKey),
            )
            .reduce((sum, t) => sum + t.amount, 0);
        const after = before + newTx.amount;

        const crossed = activeThresholds(goal.reminders).filter(
            (p) =>
                before * 100 < goal.amount * p &&
                after * 100 >= goal.amount * p,
        );
        if (crossed.length === 0) return;

        setReminders((prev) => [
            ...prev,
            {
                id: Date.now(),
                category: newTx.category,
                percent: Math.max(...crossed),
                spent: after,
                goal: goal.amount,
            },
        ]);
    }

    function addTransaction(values) {
        const cleaned = clean(values);
        checkReminders(cleaned);
        setRawTransactions((prev) => [...prev, { id: Date.now(), ...cleaned }]);
    }

    function updateTransaction(id, values) {
        setRawTransactions((prev) =>
            prev.map((t) => (t.id === id ? { ...t, ...clean(values) } : t)),
        );
    }

    function deleteTransaction(id) {
        setRawTransactions((prev) => prev.filter((t) => t.id !== id));
    }

    // ---- Goals ----
    function getCategoryGoal(category) {
        return (
            categoryGoals[category] ?? {
                amount: 0,
                reminders: DEFAULT_REMINDERS,
            }
        );
    }

    function saveCategoryGoal(category, goal) {
        setCategoryGoals((prev) => ({ ...prev, [category]: goal }));
    }

    function saveOverallGoal(amount) {
        setOverallGoal(amount);
    }

    function dismissReminder() {
        setReminders((prev) => prev.slice(1));
    }

    return (
        <FinanceContext.Provider
            value={{
                user,
                authOpen,
                openAuth,
                closeAuth,
                updateName,
                signUp,
                logIn,
                logInWithGoogle,
                logOut,
                accounts,
                addAccount,
                updateAccount,
                deleteAccount,
                categories,
                addCategory,
                deleteCategory,
                transactions,
                totalBalance,
                addTransaction,
                updateTransaction,
                deleteTransaction,
                overallGoal,
                saveOverallGoal,
                getCategoryGoal,
                saveCategoryGoal,
                reminders,
                dismissReminder,
            }}
        >
            {children}
        </FinanceContext.Provider>
    );
}

export function useFinance() {
    return useContext(FinanceContext);
}

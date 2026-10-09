import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { useFinance } from "../context/financecontext";
import { formatRupiah, formatDate, todayString } from "../utils/format";
import Modal from "../components/modal";
import TransactionFields from "../components/transactionfields";

const FILTER_GROUPS = [
    { key: "category", label: "Category" },
    { key: "account", label: "Accounts" },
];

function FilterChip({ label, onRemove }) {
    return (
        <span className="inline-flex items-center gap-2 rounded-md border border-border bg-surface px-3 py-1 text-xs text-ink">
            {label}
            <button
                type="button"
                onClick={onRemove}
                aria-label={`Remove filter ${label}`}
                className="text-muted transition-colors hover:text-accent"
            >
                ×
            </button>
        </span>
    );
}

export default function Transactions() {
    const {
        accounts,
        categories,
        transactions,
        addTransaction,
        updateTransaction,
        deleteTransaction,
    } = useFinance();
    const [searchParams, setSearchParams] = useSearchParams();

    const [popover, setPopover] = useState(null); // { id, x, y }: the small "Details" box
    const [editing, setEditing] = useState(null); // draft values of the transaction being edited
    const [adding, setAdding] = useState(null); // draft values of a new transaction
    const [query, setQuery] = useState(""); // search text
    const [filterOpen, setFilterOpen] = useState(false);
    const [filterGroup, setFilterGroup] = useState(null); // 'category' or 'account'

    // Active filters are read from the URL
    const rawCategory = searchParams.get("category");
    const categoryFilter = categories.includes(rawCategory)
        ? rawCategory
        : null;
    const rawAccount = searchParams.get("account");
    const accountFilter = accounts.some((a) => String(a.id) === rawAccount)
        ? rawAccount
        : null;
    const activeFilters = { category: categoryFilter, account: accountFilter };

    function setFilter(key, value) {
        const next = new URLSearchParams(searchParams);
        if (value === null || next.get(key) === String(value)) next.delete(key);
        else next.set(key, String(value));
        setSearchParams(next, { replace: true });
    }

    function closeFilter() {
        setFilterOpen(false);
        setFilterGroup(null);
    }

    function clearAll() {
        setQuery("");
        setSearchParams({}, { replace: true });
    }

    const accountName = (id) => accounts.find((a) => a.id === id)?.name ?? "";

    const q = query.trim().toLowerCase();
    const visible = transactions.filter((t) => {
        if (categoryFilter && t.category !== categoryFilter) return false;
        if (accountFilter && String(t.accountId) !== accountFilter)
            return false;
        if (!q) return true;
        return [
            t.name,
            t.description || "",
            t.category,
            accountName(t.accountId),
        ].some((v) => v.toLowerCase().includes(q));
    });

    const filterOptions =
        filterGroup === "category"
            ? categories.map((c) => ({ value: c, label: c }))
            : accounts.map((a) => ({ value: String(a.id), label: a.name }));
    // Escape closes whatever is open
    useEffect(() => {
        function onKeyDown(e) {
            if (e.key === "Escape") {
                setPopover(null);
                setEditing(null);
                setAdding(null);
                setFilterOpen(false);
                setFilterGroup(null);
            }
        }
        window.addEventListener("keydown", onKeyDown);
        return () => window.removeEventListener("keydown", onKeyDown);
    }, []);

    function openPopover(e, id) {
        const rect = e.currentTarget.getBoundingClientRect();
        const fromKeyboard = e.detail === 0;
        setPopover({
            id,
            x: fromKeyboard ? rect.left + 24 : e.clientX,
            y: fromKeyboard ? rect.bottom - 12 : e.clientY,
        });
    }

    function openDetails() {
        const t = transactions.find((tx) => tx.id === popover.id);
        setPopover(null);
        if (!t) return;
        setEditing({
            id: t.id,
            name: t.name,
            description: t.description || "",
            amount: String(t.amount),
            category: t.category,
            accountId: String(t.accountId),
            date: t.date,
        });
    }

    function handleDone(e) {
        e.preventDefault();
        updateTransaction(editing.id, editing);
        setEditing(null);
    }

    function handleDelete() {
        deleteTransaction(editing.id);
        setEditing(null);
    }

    function openAdd() {
        setAdding({
            name: "",
            description: "",
            amount: "",
            category: categories[0],
            accountId: String(accounts[0].id),
            date: todayString(),
        });
    }

    function handleAdd(e) {
        e.preventDefault();
        addTransaction(adding);
        setAdding(null);
    }

    const hasFilters = Boolean(categoryFilter || accountFilter || q);

    return (
        <main className="ml-60 px-10 py-10 pb-28">
            <h1 className="mb-1 font-display text-2xl font-semibold text-ink">
                Transactions
            </h1>
            <p className="mb-6 text-sm text-muted">
                Select a transaction to view or edit it.
            </p>

            {/* Filter button + search box */}
            <div className="mb-4 flex items-center gap-3">
                <div className="relative">
                    <button
                        type="button"
                        onClick={() => {
                            setFilterOpen(true);
                            setFilterGroup(null);
                        }}
                        className="rounded-md border border-border px-4 py-2.5 text-sm font-medium text-ink transition-colors hover:border-accent hover:bg-surface-raised"
                    >
                        Filter
                    </button>

                    {filterOpen && (
                        <>
                            <div
                                className="fixed inset-0 z-20"
                                onClick={closeFilter}
                            />
                            <div className="absolute left-full top-0 z-30 ml-2 flex items-start gap-2">
                                <div className="w-36 rounded-md border border-border bg-surface-raised py-1 shadow-lg">
                                    {FILTER_GROUPS.map((group) => (
                                        <button
                                            key={group.key}
                                            type="button"
                                            onClick={() =>
                                                setFilterGroup(group.key)
                                            }
                                            className={`flex w-full items-center justify-between px-4 py-2 text-left text-sm transition-colors hover:bg-surface ${
                                                filterGroup === group.key
                                                    ? "text-accent"
                                                    : "text-ink"
                                            }`}
                                        >
                                            {group.label}
                                            {activeFilters[group.key] && (
                                                <span className="h-1.5 w-1.5 rounded-full bg-accent" />
                                            )}
                                        </button>
                                    ))}
                                </div>

                                {filterGroup && (
                                    <div className="w-44 rounded-md border border-border bg-surface-raised py-1 shadow-lg">
                                        {filterOptions.map((option) => {
                                            const selected =
                                                activeFilters[filterGroup] ===
                                                option.value;
                                            return (
                                                <button
                                                    key={option.value}
                                                    type="button"
                                                    onClick={() =>
                                                        setFilter(
                                                            filterGroup,
                                                            option.value,
                                                        )
                                                    }
                                                    className={`flex w-full items-center justify-between px-4 py-2 text-left text-sm transition-colors hover:bg-surface ${
                                                        selected
                                                            ? "text-accent"
                                                            : "text-ink"
                                                    }`}
                                                >
                                                    {option.label}
                                                    {selected && (
                                                        <span aria-hidden="true">
                                                            ✓
                                                        </span>
                                                    )}
                                                </button>
                                            );
                                        })}
                                    </div>
                                )}
                            </div>
                        </>
                    )}
                </div>

                <input
                    type="text"
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder="Search transactions"
                    aria-label="Search transactions"
                    className="w-full max-w-sm rounded-md border border-border bg-surface px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none"
                />
            </div>

            {(categoryFilter || accountFilter) && (
                <div className="mb-4 flex flex-wrap items-center gap-2">
                    {categoryFilter && (
                        <FilterChip
                            label={`Category: ${categoryFilter}`}
                            onRemove={() => setFilter("category", null)}
                        />
                    )}
                    {accountFilter && (
                        <FilterChip
                            label={`Account: ${accountName(Number(accountFilter))}`}
                            onRemove={() => setFilter("account", null)}
                        />
                    )}
                </div>
            )}

            {transactions.length === 0 ? (
                <p className="text-sm text-muted">
                    No transactions yet. Use "Add transaction" to record your
                    first one.
                </p>
            ) : visible.length === 0 ? (
                <div>
                    <p className="text-sm text-muted">
                        No transactions match your search or filters.
                    </p>
                    {hasFilters && (
                        <button
                            type="button"
                            onClick={clearAll}
                            className="mt-2 text-xs text-accent hover:underline"
                        >
                            Clear filters
                        </button>
                    )}
                </div>
            ) : (
                <div className="border-t border-border">
                    {visible.map((t) => (
                        <button
                            key={t.id}
                            type="button"
                            onClick={(e) => openPopover(e, t.id)}
                            className="group flex w-full items-center justify-between border-b border-border py-3 text-left"
                        >
                            <div>
                                <p className="text-sm text-ink transition-colors group-hover:text-accent">
                                    {t.name}
                                </p>
                                <p className="text-xs text-muted">
                                    {t.category}
                                </p>
                            </div>
                            <div className="text-right">
                                <p className="font-display text-sm text-ink">
                                    -{formatRupiah(t.amount)}
                                </p>
                                <p className="text-xs text-muted">
                                    {formatDate(t.date)}
                                </p>
                            </div>
                        </button>
                    ))}
                </div>
            )}

            {/* Small "Details" box that appears where the user clicked */}
            {popover && (
                <>
                    <div
                        className="fixed inset-0 z-30"
                        onClick={() => setPopover(null)}
                    />
                    <div
                        className="fixed z-40 rounded-md border border-border bg-surface-raised shadow-lg"
                        style={{
                            left: Math.min(popover.x, window.innerWidth - 140),
                            top: Math.min(
                                popover.y + 8,
                                window.innerHeight - 60,
                            ),
                        }}
                    >
                        <button
                            type="button"
                            autoFocus
                            onClick={openDetails}
                            className="w-full px-5 py-2 text-left text-sm text-ink transition-colors hover:text-accent"
                        >
                            Details
                        </button>
                    </div>
                </>
            )}

            {/* Big centered box: edit one transaction */}
            {editing && (
                <Modal title="Transaction details">
                    <form onSubmit={handleDone}>
                        <TransactionFields
                            values={editing}
                            accounts={accounts}
                            onChange={(field, value) =>
                                setEditing((prev) => ({
                                    ...prev,
                                    [field]: value,
                                }))
                            }
                        />
                        <div className="mt-8 flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={handleDelete}
                                className="rounded-md border border-border px-5 py-2.5 text-sm font-medium text-muted transition-colors hover:border-danger hover:text-danger"
                            >
                                Delete
                            </button>
                            <button
                                type="submit"
                                className="rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90"
                            >
                                Done
                            </button>
                        </div>
                    </form>
                </Modal>
            )}

            {/* Big centered box: add a new transaction */}
            {adding && (
                <Modal title="Add transaction">
                    <form onSubmit={handleAdd}>
                        <TransactionFields
                            values={adding}
                            accounts={accounts}
                            onChange={(field, value) =>
                                setAdding((prev) => ({
                                    ...prev,
                                    [field]: value,
                                }))
                            }
                        />
                        <div className="mt-8 flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={() => setAdding(null)}
                                className="rounded-md border border-border px-5 py-2.5 text-sm font-medium text-muted transition-colors hover:bg-surface-raised hover:text-ink"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                className="rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90"
                            >
                                Add
                            </button>
                        </div>
                    </form>
                </Modal>
            )}

            {/* Small button, bottom right */}
            <button
                type="button"
                onClick={openAdd}
                className="fixed bottom-8 right-8 rounded-md bg-accent px-4 py-2.5 text-sm font-medium text-bg shadow-lg transition-opacity hover:opacity-90"
            >
                Add transaction
            </button>
        </main>
    );
}

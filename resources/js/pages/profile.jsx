import { useEffect, useState } from "react";
import { useFinance, ACCOUNT_TYPES } from "../context/financecontext";
import { formatRupiah } from "../utils/format";
import Modal from "../components/modal";

const inputClass =
    "w-full rounded-md border border-border bg-bg px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none";
const labelClass = "mb-1.5 block text-sm font-medium text-muted";
const primaryButton =
    "rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40";
const secondaryButton =
    "rounded-md border border-border px-5 py-2.5 text-sm font-medium text-muted transition-colors hover:bg-surface-raised hover:text-ink";

function Section({ title, description, action, children }) {
    return (
        <section className="mb-6 rounded-md border border-border bg-surface p-6">
            <div className="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 className="font-display text-base font-semibold text-accent">
                        {title}
                    </h2>
                    {description && (
                        <p className="mt-1 text-xs text-muted">{description}</p>
                    )}
                </div>
                {action}
            </div>
            {children}
        </section>
    );
}

/* ---------- Name ---------- */

function NameSection() {
    const { user, updateName } = useFinance();
    const [name, setName] = useState(user.name);
    const [saved, setSaved] = useState(false);

    useEffect(() => {
        setName(user.name);
    }, [user.name]);

    function handleSubmit(e) {
        e.preventDefault();
        if (!name.trim()) return;
        updateName(name);
        setSaved(true);
    }

    return (
        <Section
            title="Name"
            description="Shown in the greeting on your dashboard."
        >
            <form onSubmit={handleSubmit} className="flex items-end gap-3">
                <div className="flex-1">
                    <label htmlFor="profile-name" className={labelClass}>
                        Your name
                    </label>
                    <input
                        id="profile-name"
                        type="text"
                        value={name}
                        onChange={(e) => {
                            setName(e.target.value);
                            setSaved(false);
                        }}
                        className={inputClass}
                        required
                    />
                </div>
                <button
                    type="submit"
                    disabled={!name.trim() || name.trim() === user.name}
                    className={primaryButton}
                >
                    Save
                </button>
            </form>
            {saved && (
                <p role="status" className="mt-3 text-xs text-accent">
                    Name saved.
                </p>
            )}
        </Section>
    );
}

/* ---------- Login info ---------- */

function LoginSection() {
    const { user, logOut } = useFinance();

    return (
        <Section title="Login" description="How you sign in to this app.">
            <div className="flex items-center justify-between gap-4">
                <div className="min-w-0">
                    <p className="text-sm text-ink">
                        {user.method === "google"
                            ? "Google account"
                            : "Email account"}
                    </p>
                    <p className="truncate text-xs text-muted">{user.email}</p>
                </div>
                <button
                    type="button"
                    onClick={logOut}
                    className={secondaryButton}
                >
                    Log out
                </button>
            </div>
        </Section>
    );
}

/* ---------- Payment accounts ---------- */

function AccountModal({ account, onClose }) {
    const { accounts, transactions, addAccount, updateAccount, deleteAccount } =
        useFinance();
    const isEdit = Boolean(account);

    const [values, setValues] = useState({
        name: account?.name ?? "",
        type: account?.type ?? ACCOUNT_TYPES[0],
        balance: account ? String(account.balance) : "",
    });

    const txCount = isEdit
        ? transactions.filter((t) => t.accountId === account.id).length
        : 0;
    const isLastAccount = accounts.length <= 1;
    const canDelete = isEdit && txCount === 0 && !isLastAccount;

    function update(field, value) {
        setValues((prev) => ({ ...prev, [field]: value }));
    }

    function handleSubmit(e) {
        e.preventDefault();
        if (!values.name.trim()) return;
        if (isEdit) updateAccount(account.id, values);
        else addAccount(values);
        onClose();
    }

    function handleDelete() {
        if (deleteAccount(account.id)) onClose();
    }

    return (
        <Modal
            title={isEdit ? "Edit account" : "Add account"}
            onClose={onClose}
        >
            <form onSubmit={handleSubmit}>
                <div className="space-y-5">
                    <div>
                        <label htmlFor="account-name" className={labelClass}>
                            Account name
                        </label>
                        <input
                            id="account-name"
                            type="text"
                            placeholder="e.g. Bank Mandiri"
                            value={values.name}
                            onChange={(e) => update("name", e.target.value)}
                            className={inputClass}
                            required
                        />
                    </div>

                    <div>
                        <label htmlFor="account-type" className={labelClass}>
                            Type
                        </label>
                        <select
                            id="account-type"
                            value={values.type}
                            onChange={(e) => update("type", e.target.value)}
                            className={inputClass}
                        >
                            {ACCOUNT_TYPES.map((t) => (
                                <option key={t} value={t}>
                                    {t}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label htmlFor="account-balance" className={labelClass}>
                            {isEdit ? "Balance (Rp)" : "Starting balance (Rp)"}
                        </label>
                        <input
                            id="account-balance"
                            type="number"
                            placeholder="0"
                            value={values.balance}
                            onChange={(e) => update("balance", e.target.value)}
                            className={inputClass}
                            required
                        />
                    </div>
                </div>

                {isEdit && !canDelete && (
                    <p className="mt-5 text-xs text-muted">
                        {txCount > 0
                            ? `This account has ${txCount} transaction${txCount === 1 ? "" : "s"}. Change or delete ${txCount === 1 ? "it" : "them"} on the Transactions page before removing the account.`
                            : "You need at least one account."}
                    </p>
                )}

                <div className="mt-8 flex justify-end gap-3">
                    {isEdit ? (
                        <>
                            <button
                                type="button"
                                onClick={handleDelete}
                                disabled={!canDelete}
                                className="rounded-md border border-border px-5 py-2.5 text-sm font-medium text-muted transition-colors disabled:cursor-not-allowed disabled:opacity-40 enabled:hover:border-danger enabled:hover:text-danger"
                            >
                                Delete
                            </button>
                            <button type="submit" className={primaryButton}>
                                Done
                            </button>
                        </>
                    ) : (
                        <>
                            <button
                                type="button"
                                onClick={onClose}
                                className={secondaryButton}
                            >
                                Cancel
                            </button>
                            <button type="submit" className={primaryButton}>
                                Add
                            </button>
                        </>
                    )}
                </div>
            </form>
        </Modal>
    );
}

function AccountsSection() {
    const { accounts } = useFinance();
    const [modal, setModal] = useState(null); // 'add' or an account object

    return (
        <Section
            title="Payment accounts"
            description="Your wallets and bank accounts. They show on the dashboard and when you add a transaction."
            action={
                <button
                    type="button"
                    onClick={() => setModal("add")}
                    className={secondaryButton}
                >
                    Add account
                </button>
            }
        >
            <div className="border-t border-border">
                {accounts.map((a) => (
                    <button
                        key={a.id}
                        type="button"
                        onClick={() => setModal(a)}
                        className="group flex w-full items-center justify-between border-b border-border py-3 text-left"
                    >
                        <div>
                            <p className="text-sm text-ink transition-colors group-hover:text-accent">
                                {a.name}
                            </p>
                            <p className="text-xs text-muted">{a.type}</p>
                        </div>
                        <p className="font-display text-sm text-ink">
                            {formatRupiah(a.balance)}
                        </p>
                    </button>
                ))}
            </div>

            {modal && (
                <AccountModal
                    key={modal === "add" ? "add" : modal.id}
                    account={modal === "add" ? null : modal}
                    onClose={() => setModal(null)}
                />
            )}
        </Section>
    );
}
/* ---------- Categories ---------- */

function CategoriesSection() {
    const { categories, transactions, addCategory, deleteCategory } =
        useFinance();
    const [name, setName] = useState("");
    const [error, setError] = useState("");

    const isLast = categories.length <= 1;

    function countFor(category) {
        return transactions.filter((t) => t.category === category).length;
    }

    function handleAdd(e) {
        e.preventDefault();
        const result = addCategory(name);
        if (!result.ok) {
            setError(result.error);
            return;
        }
        setName("");
        setError("");
    }

    return (
        <Section
            title="Categories"
            description="The spending categories you can pick when adding a transaction."
        >
            <form onSubmit={handleAdd} className="mb-5 flex items-start gap-3">
                <div className="flex-1">
                    <label htmlFor="category-name" className="sr-only">
                        New category name
                    </label>
                    <input
                        id="category-name"
                        type="text"
                        placeholder="e.g. Education"
                        value={name}
                        onChange={(e) => {
                            setName(e.target.value);
                            setError("");
                        }}
                        aria-invalid={Boolean(error)}
                        aria-describedby={error ? "category-error" : undefined}
                        className={inputClass}
                    />
                    {error && (
                        <p
                            id="category-error"
                            role="alert"
                            className="mt-1.5 text-xs text-danger"
                        >
                            {error}
                        </p>
                    )}
                </div>
                <button
                    type="submit"
                    disabled={!name.trim()}
                    className={primaryButton}
                >
                    Add
                </button>
            </form>

            <div className="border-t border-border">
                {categories.map((category) => {
                    const count = countFor(category);
                    const canDelete = count === 0 && !isLast;
                    return (
                        <div
                            key={category}
                            className="flex items-center justify-between gap-4 border-b border-border py-3"
                        >
                            <div>
                                <p className="text-sm text-ink">{category}</p>
                                <p className="text-xs text-muted">
                                    {count === 0
                                        ? "No transactions"
                                        : `${count} transaction${count === 1 ? "" : "s"}`}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => deleteCategory(category)}
                                disabled={!canDelete}
                                aria-label={`Delete ${category}`}
                                className="rounded-md border border-border px-4 py-2 text-sm font-medium text-muted transition-colors disabled:cursor-not-allowed disabled:opacity-40 enabled:hover:border-danger enabled:hover:text-danger"
                            >
                                Delete
                            </button>
                        </div>
                    );
                })}
            </div>

            <p className="mt-4 text-xs text-muted">
                A category that has transactions can't be deleted. Change or
                delete those transactions on the Transactions page first. You
                also need at least one category.
            </p>
        </Section>
    );
}

/* ---------- Page ---------- */

export default function Profile() {
    const { user, openAuth } = useFinance();

    // Opening this page while logged out shows the login box right away
    useEffect(() => {
        if (!user.signedIn) openAuth();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return (
        <main className="ml-60 px-10 py-10">
            <h1 className="mb-8 font-display text-2xl font-semibold text-ink">
                Profile
            </h1>

            <div className="max-w-2xl">
                {user.signedIn ? (
                    <>
                        <NameSection />
                        <LoginSection />
                        <AccountsSection />
                        <CategoriesSection />
                    </>
                ) : (
                    <section className="rounded-md border border-border bg-surface p-6">
                        <h2 className="font-display text-base font-semibold text-accent">
                            You are not logged in
                        </h2>
                        <p className="mt-1 text-sm text-muted">
                            Log in to manage your name and payment accounts.
                        </p>
                        <button
                            type="button"
                            onClick={openAuth}
                            className={`${primaryButton} mt-5`}
                        >
                            Log in
                        </button>
                    </section>
                )}
            </div>
        </main>
    );
}

import { useState } from "react";
import { Link } from "react-router-dom";
import { useFinance } from "../context/financecontext";
import StatCard from "../components/statcard";
import OutcomeToday from "../components/outcomestoday";
import AdvisorChat from "../components/advisorchat";
import Modal from "../components/modal";
import { formatRupiah, formatDate, currentMonthKey } from "../utils/format";

function greeting() {
    const hour = new Date().getHours();
    if (hour < 12) return "Good morning";
    if (hour < 18) return "Good afternoon";
    return "Good evening";
}

function DetailRow({ label, value }) {
    return (
        <div className="flex items-center justify-between gap-3 py-1.5 text-sm">
            <span className="truncate text-ink">{label}</span>
            <span className="shrink-0 text-muted">{value}</span>
        </div>
    );
}

function CategoryRow({ category, total, onSelect }) {
    return (
        <button
            type="button"
            onClick={() => onSelect(category)}
            className="flex w-full items-center justify-between gap-3 py-1.5 text-left text-sm text-ink transition-colors hover:text-accent"
        >
            <span className="truncate">{category}</span>
            <span className="shrink-0 text-muted">-{formatRupiah(total)}</span>
        </button>
    );
}

function EmptyNote({ children }) {
    return <p className="py-1.5 text-sm text-muted">{children}</p>;
}

function DetailsLink() {
    return (
        <Link
            to="/transactions"
            className="mt-2 inline-block text-xs text-accent hover:underline"
        >
            Details →
        </Link>
    );
}

export default function Dashboard() {
    const { user, accounts, transactions, totalBalance } = useFinance();
    const [categoryPopup, setCategoryPopup] = useState(null); // category name

    const monthKey = currentMonthKey();
    const thisMonth = transactions.filter((t) =>
        (t.date || "").startsWith(monthKey),
    );
    const spentThisMonth = thisMonth.reduce((sum, t) => sum + t.amount, 0);
    const latestThree = thisMonth.slice(0, 3);

    const topCategories = Object.values(
        thisMonth.reduce((acc, t) => {
            acc[t.category] = acc[t.category] || {
                category: t.category,
                total: 0,
            };
            acc[t.category].total += t.amount;
            return acc;
        }, {}),
    )
        .sort((a, b) => b.total - a.total)
        .slice(0, 3);

    const latestInCategory = categoryPopup
        ? transactions.filter((t) => t.category === categoryPopup).slice(0, 3)
        : [];

    return (
        <main className="ml-60 px-10 py-10">
            <div className="xl:grid xl:grid-cols-[minmax(0,1fr)_20rem] xl:gap-8">
                <div className="min-w-0">
                    <h2 className="mb-1 font-display text-3xl font-semibold text-ink">
                        {greeting()},{" "}
                        <span className="text-accent">{user.name}</span>
                    </h2>
                    <p className="mb-3 text-sm text-muted">
                        Here is your balance for this month
                    </p>
                    <h1 className="mb-10 font-display text-5xl font-semibold text-ink">
                        {formatRupiah(totalBalance)}
                    </h1>

                    <OutcomeToday />

                    <div className="mb-10 grid grid-cols-3 gap-4">
                        <StatCard
                            label="This month"
                            value={formatRupiah(spentThisMonth)}
                        >
                            {latestThree.length === 0 ? (
                                <EmptyNote>
                                    No transactions this month.
                                </EmptyNote>
                            ) : (
                                latestThree.map((t) => (
                                    <DetailRow
                                        key={t.id}
                                        label={t.name}
                                        value={`-${formatRupiah(t.amount)}`}
                                    />
                                ))
                            )}
                            <DetailsLink />
                        </StatCard>

                        <StatCard
                            label="Top category"
                            value={topCategories[0]?.category ?? "None yet"}
                        >
                            {topCategories.length === 0 ? (
                                <EmptyNote>No spending this month.</EmptyNote>
                            ) : (
                                topCategories.map((c) => (
                                    <CategoryRow
                                        key={c.category}
                                        category={c.category}
                                        total={c.total}
                                        onSelect={setCategoryPopup}
                                    />
                                ))
                            )}
                            <DetailsLink />
                        </StatCard>

                        <StatCard
                            label="Accounts"
                            value={`${accounts.length} active`}
                        >
                            {accounts.map((a) => (
                                <DetailRow
                                    key={a.id}
                                    label={a.name}
                                    value={formatRupiah(a.balance)}
                                />
                            ))}
                            <Link
                                to="/profile"
                                className="mt-2 inline-block text-xs text-accent hover:underline"
                            >
                                Add +
                            </Link>
                        </StatCard>
                    </div>

                    <h3 className="mb-3 text-sm font-medium text-accent">
                        Recent transactions
                    </h3>
                    <div className="border-t border-border">
                        {transactions.length === 0 && (
                            <p className="py-3 text-sm text-muted">
                                No transactions yet.
                            </p>
                        )}
                        {transactions.slice(0, 5).map((t) => (
                            <Link
                                key={t.id}
                                to="/transactions"
                                className="group flex items-center justify-between border-b border-border py-3"
                            >
                                <div>
                                    <p className="text-sm text-ink transition-colors group-hover:text-accent">
                                        {t.name}
                                    </p>
                                    <p className="text-xs text-muted">
                                        {t.category}
                                    </p>
                                </div>
                                <p className="font-display text-sm text-ink">
                                    -{formatRupiah(t.amount)}
                                </p>
                            </Link>
                        ))}
                    </div>
                </div>

                <aside className="mt-10 h-[32rem] xl:sticky xl:top-10 xl:mt-0 xl:h-[calc(100vh-5rem)] xl:self-start">
                    <AdvisorChat />
                </aside>
            </div>

            {/* Latest transactions of the category the user clicked */}
            {categoryPopup && (
                <Modal
                    title={categoryPopup}
                    maxWidth="max-w-md"
                    onClose={() => setCategoryPopup(null)}
                >
                    <p className="mb-3 text-xs text-muted">
                        Latest transactions
                    </p>
                    <div className="border-t border-border">
                        {latestInCategory.map((t) => (
                            <div
                                key={t.id}
                                className="flex items-center justify-between border-b border-border py-3"
                            >
                                <div>
                                    <p className="text-sm text-ink">{t.name}</p>
                                    <p className="text-xs text-muted">
                                        {formatDate(t.date)}
                                    </p>
                                </div>
                                <p className="font-display text-sm text-ink">
                                    -{formatRupiah(t.amount)}
                                </p>
                            </div>
                        ))}
                    </div>
                    <Link
                        to={`/transactions?category=${encodeURIComponent(categoryPopup)}`}
                        className="mt-4 inline-block text-xs text-accent hover:underline"
                    >
                        Details →
                    </Link>
                </Modal>
            )}
        </main>
    );
}

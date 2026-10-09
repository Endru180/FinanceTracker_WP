import { useState } from "react";
import { useFinance } from "../context/financecontext";
import { formatRupiah } from "../utils/format";
import {
    monthTransactions,
    totalsByCategory,
    sumAmount,
} from "../utils/spendings";
import GaugeChart from "../components/gaugechart";
import GoalSettingsModal from "../components/goalsettingmodal";

function GoalRow({ category, spent, goal, onClick }) {
    const over = goal > 0 && spent > goal;
    return (
        <button
            type="button"
            onClick={onClick}
            className="group block w-full border-b border-border py-3 text-left"
        >
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm text-ink transition-colors group-hover:text-accent">
                        {category}
                    </p>
                    <p className="text-xs text-muted">
                        {formatRupiah(spent)} spent this month
                    </p>
                </div>
                <div className="text-right">
                    <p className="font-display text-sm text-accent">
                        {formatRupiah(goal)}
                    </p>
                    <p className="text-xs text-muted">Goal</p>
                </div>
            </div>
            {goal > 0 && (
                <div className="mt-2 h-1 rounded-full bg-surface-raised">
                    <div
                        className={`h-1 rounded-full ${over ? "bg-danger" : "bg-accent"}`}
                        style={{ width: `${Math.min(spent / goal, 1) * 100}%` }}
                    />
                </div>
            )}
        </button>
    );
}

export default function Goals() {
    const {
        categories,
        transactions,
        overallGoal,
        saveOverallGoal,
        getCategoryGoal,
        saveCategoryGoal,
    } = useFinance();

    const [view, setView] = useState("overview"); // 'overview' or 'all'
    const [settingsFor, setSettingsFor] = useState(null); // category name or 'overall'

    const month = monthTransactions(transactions);
    const spentThisMonth = sumAmount(month);
    const monthTotals = totalsByCategory(month);
    const topMonth = monthTotals.slice(0, 3);
    const monthByCategory = Object.fromEntries(
        monthTotals.map((c) => [c.category, c.total]),
    );

    function handleSaveGoal(goal) {
        if (settingsFor === "overall") saveOverallGoal(goal.amount);
        else saveCategoryGoal(settingsFor, goal);
        setSettingsFor(null);
    }

    const overview = (
        <>
            <h1 className="mb-8 font-display text-2xl font-semibold text-ink">
                Goals
            </h1>

            <h2 className="mb-3 text-sm font-medium text-accent">
                Budget reminder
            </h2>
            <div className="mb-10 max-w-md">
                <GaugeChart
                    spent={spentThisMonth}
                    goal={overallGoal}
                    onEditGoal={() => setSettingsFor("overall")}
                />
            </div>

            <h2 className="mb-3 text-sm font-medium text-accent">Goal</h2>
            <div className="max-w-2xl border-t border-border">
                {topMonth.length === 0 ? (
                    <p className="py-3 text-sm text-muted">
                        Add a transaction to see your biggest categories here.
                    </p>
                ) : (
                    topMonth.map((c) => (
                        <GoalRow
                            key={c.category}
                            category={c.category}
                            spent={c.total}
                            goal={getCategoryGoal(c.category).amount}
                            onClick={() => setSettingsFor(c.category)}
                        />
                    ))
                )}
            </div>
            <button
                type="button"
                onClick={() => setView("all")}
                className="mt-3 text-xs text-accent hover:underline"
            >
                View detail →
            </button>
        </>
    );

    const allGoals = (
        <>
            <button
                type="button"
                onClick={() => setView("overview")}
                aria-label="Back to goals"
                className="mb-6 flex h-9 w-9 items-center justify-center rounded-md border border-border text-muted transition-colors hover:border-accent hover:text-ink"
            >
                &lt;
            </button>
            <h1 className="mb-1 font-display text-2xl font-semibold text-ink">
                Category goals
            </h1>
            <p className="mb-8 text-sm text-muted">
                Select a category to set its goal and reminders.
            </p>

            <div className="max-w-2xl border-t border-border">
                {categories.map((category) => (
                    <GoalRow
                        key={category}
                        category={category}
                        spent={monthByCategory[category] || 0}
                        goal={getCategoryGoal(category).amount}
                        onClick={() => setSettingsFor(category)}
                    />
                ))}
            </div>
        </>
    );

    return (
        <main className="ml-60 px-10 py-10">
            {view === "all" ? allGoals : overview}

            {settingsFor && (
                <GoalSettingsModal
                    key={settingsFor}
                    title={
                        settingsFor === "overall"
                            ? "Overall goal"
                            : `${settingsFor} goal`
                    }
                    description={
                        settingsFor === "overall"
                            ? "The most you plan to spend across all categories this month."
                            : `The most you plan to spend on ${settingsFor} this month.`
                    }
                    goal={
                        settingsFor === "overall"
                            ? { amount: overallGoal }
                            : getCategoryGoal(settingsFor)
                    }
                    withReminders={settingsFor !== "overall"}
                    onSave={handleSaveGoal}
                    onClose={() => setSettingsFor(null)}
                />
            )}
        </main>
    );
}

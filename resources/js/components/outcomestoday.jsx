import { useFinance } from "../context/financecontext";
import { formatRupiah } from "../utils/format";
import {
    todayTransactions,
    totalsByCategory,
    sumAmount,
} from "../utils/spendings";
import DonutChart, { CHART_COLORS, OTHER_COLOR } from "./donutchart";

export default function OutcomeToday() {
    const { transactions } = useFinance();

    const today = todayTransactions(transactions);
    const spentToday = sumAmount(today);
    const top = totalsByCategory(today).slice(0, 3);
    const topSum = top.reduce((sum, c) => sum + c.total, 0);

    const segments = [
        ...top.map((c, i) => ({ value: c.total, color: CHART_COLORS[i] })),
        { value: spentToday - topSum, color: OTHER_COLOR },
    ];

    return (
        <section className="mb-10 rounded-md border border-border bg-surface p-6">
            <div className="flex items-start justify-between gap-6">
                <div>
                    <h3 className="text-sm font-medium text-accent">
                        Outcome today
                    </h3>
                    <p className="mt-3 font-display text-4xl font-semibold text-ink">
                        {formatRupiah(spentToday)}
                    </p>
                </div>
                <DonutChart segments={segments} />
            </div>

            <div className="mt-6 space-y-2">
                {top.length === 0 ? (
                    <p className="text-sm text-muted">No spending today.</p>
                ) : (
                    top.map((c, i) => (
                        <div
                            key={c.category}
                            className="flex items-center justify-between text-sm"
                        >
                            <span className="flex items-center gap-3 text-ink">
                                <span
                                    className="h-2.5 w-2.5 rounded-full"
                                    style={{ backgroundColor: CHART_COLORS[i] }}
                                />
                                {c.category}
                            </span>
                            <span className="font-display text-ink">
                                {formatRupiah(c.total)}
                            </span>
                        </div>
                    ))
                )}
            </div>
        </section>
    );
}

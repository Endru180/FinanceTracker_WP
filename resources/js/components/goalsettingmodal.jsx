import { useState } from "react";
import Modal from "./modal";
import { DEFAULT_REMINDERS } from "../utils/spendings";
import { formatRupiah } from "../utils/format";

const PRESETS = [
    { key: "half", label: "Half of the budget is used", percent: 50 },
    { key: "threeQuarters", label: "3/4 of the budget is used", percent: 75 },
    { key: "ninetyPercent", label: "9/10 of the budget is used", percent: 90 },
];

const inputClass =
    "w-full rounded-md border border-border bg-surface px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none";

export default function GoalSettingsModal({
    title,
    description,
    goal,
    withReminders = true,
    onSave,
    onClose,
}) {
    const [amount, setAmount] = useState(
        goal.amount > 0 ? String(goal.amount) : "",
    );
    const [reminders, setReminders] = useState({
        ...DEFAULT_REMINDERS,
        ...goal.reminders,
    });
    const [error, setError] = useState("");

    const goalNumber = Math.max(0, Number(amount) || 0);

    function toggle(key) {
        setReminders((prev) => ({ ...prev, [key]: !prev[key] }));
    }

    function amountAt(percent) {
        return goalNumber > 0
            ? formatRupiah(Math.round((goalNumber * percent) / 100))
            : "";
    }

    function handleSubmit(e) {
        e.preventDefault();
        if (withReminders && reminders.customEnabled) {
            const percent = Number(reminders.customPercent);
            if (!(percent >= 1 && percent <= 100)) {
                setError("Enter a custom reminder between 1 and 100.");
                return;
            }
        }
        onSave({ amount: goalNumber, reminders });
    }

    return (
        <Modal title={title} maxWidth="max-w-xl" onClose={onClose}>
            <form onSubmit={handleSubmit} className="space-y-4">
                <section className="rounded-md border border-border bg-bg p-4">
                    <h3 className="text-sm font-medium text-ink">Goal</h3>
                    <p className="mb-3 mt-1 text-xs text-muted">
                        {description}
                    </p>
                    <label htmlFor="goal-amount" className="sr-only">
                        Goal amount in Rp
                    </label>
                    <input
                        id="goal-amount"
                        type="number"
                        min="0"
                        placeholder="0"
                        value={amount}
                        onChange={(e) => setAmount(e.target.value)}
                        className={inputClass}
                    />
                </section>

                {withReminders && (
                    <section className="rounded-md border border-border bg-bg p-4">
                        <h3 className="text-sm font-medium text-ink">Timer</h3>
                        <p className="mb-3 mt-1 text-xs text-muted">
                            {goalNumber > 0
                                ? "Remind me when:"
                                : "Set a goal above to turn reminders on."}
                        </p>

                        <div className="space-y-3">
                            {PRESETS.map((p) => (
                                <label
                                    key={p.key}
                                    className="flex cursor-pointer items-center justify-between gap-3 text-sm text-ink"
                                >
                                    <span className="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            checked={reminders[p.key]}
                                            onChange={() => toggle(p.key)}
                                            className="h-4 w-4 accent-accent"
                                        />
                                        {p.label}
                                    </span>
                                    <span className="text-xs text-muted">
                                        {amountAt(p.percent)}
                                    </span>
                                </label>
                            ))}

                            <div className="flex items-center justify-between gap-3 text-sm text-ink">
                                <label className="flex cursor-pointer items-center gap-3">
                                    <input
                                        type="checkbox"
                                        checked={reminders.customEnabled}
                                        onChange={() => toggle("customEnabled")}
                                        className="h-4 w-4 accent-accent"
                                    />
                                    Custom
                                </label>
                                <div className="flex items-center gap-2">
                                    <input
                                        type="number"
                                        min="1"
                                        max="100"
                                        placeholder="60"
                                        aria-label="Custom reminder percentage"
                                        value={reminders.customPercent}
                                        disabled={!reminders.customEnabled}
                                        onChange={(e) =>
                                            setReminders((prev) => ({
                                                ...prev,
                                                customPercent: e.target.value,
                                            }))
                                        }
                                        className="w-20 rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-ink focus:border-accent focus:outline-none disabled:opacity-40"
                                    />
                                    <span className="text-muted">
                                        % of the budget
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>
                )}

                {error && (
                    <p role="alert" className="text-sm text-danger">
                        {error}
                    </p>
                )}

                <div className="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-md border border-border px-5 py-2.5 text-sm font-medium text-muted transition-colors hover:bg-surface-raised hover:text-ink"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        className="rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90"
                    >
                        Save
                    </button>
                </div>
            </form>
        </Modal>
    );
}

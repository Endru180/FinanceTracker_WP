import { useFinance } from "../context/financecontext";
import { formatRupiah } from "../utils/format";
import Modal from "./modal";

export default function ReminderPopup() {
    const { reminders, dismissReminder } = useFinance();
    const reminder = reminders[0];

    if (!reminder) return null;

    return (
        <Modal
            title="Budget reminder"
            maxWidth="max-w-sm"
            onClose={dismissReminder}
        >
            <p className="text-sm text-ink">
                You've reached {reminder.percent}% of your {reminder.category}{" "}
                goal this month.
            </p>
            <p className="mt-2 text-sm text-muted">
                {formatRupiah(reminder.spent)} spent of{" "}
                {formatRupiah(reminder.goal)}.
            </p>
            <div className="mt-8 flex justify-end">
                <button
                    type="button"
                    onClick={dismissReminder}
                    className="rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-bg transition-opacity hover:opacity-90"
                >
                    Got it
                </button>
            </div>
        </Modal>
    );
}

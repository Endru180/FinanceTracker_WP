import { useFinance } from "../context/financecontext";
import { formatRupiah } from "../utils/format";

const inputClass =
    "w-full rounded-md border border-border bg-bg px-3 py-2.5 text-sm text-ink placeholder:text-muted transition-colors focus:border-accent focus:outline-none";
const labelClass = "mb-1.5 block text-sm font-medium text-muted";

export default function TransactionFields({ values, onChange, accounts }) {
    const { categories } = useFinance();

    return (
        <div className="space-y-5">
            <div>
                <label htmlFor="tx-name" className={labelClass}>
                    Name
                </label>
                <input
                    id="tx-name"
                    type="text"
                    placeholder="e.g. Lunch at Warteg"
                    value={values.name}
                    onChange={(e) => onChange("name", e.target.value)}
                    className={inputClass}
                />
            </div>

            <div>
                <label htmlFor="tx-description" className={labelClass}>
                    Description
                </label>
                <textarea
                    id="tx-description"
                    rows={3}
                    placeholder="Optional notes about this transaction"
                    value={values.description ?? ""}
                    onChange={(e) => onChange("description", e.target.value)}
                    className={`${inputClass} resize-y`}
                />
            </div>

            <div>
                <label htmlFor="tx-amount" className={labelClass}>
                    Amount (Rp)
                </label>
                <input
                    id="tx-amount"
                    type="number"
                    min="1"
                    placeholder="0"
                    value={values.amount}
                    onChange={(e) => onChange("amount", e.target.value)}
                    className={inputClass}
                    required
                />
            </div>

            <div>
                <label htmlFor="tx-category" className={labelClass}>
                    Category
                </label>
                <select
                    id="tx-category"
                    value={values.category}
                    onChange={(e) => onChange("category", e.target.value)}
                    className={inputClass}
                >
                    {categories.map((c) => (
                        <option key={c} value={c}>
                            {c}
                        </option>
                    ))}
                </select>
            </div>

            <div>
                <label htmlFor="tx-account" className={labelClass}>
                    Account
                </label>
                <select
                    id="tx-account"
                    value={values.accountId}
                    onChange={(e) => onChange("accountId", e.target.value)}
                    className={inputClass}
                >
                    {accounts.map((a) => (
                        <option key={a.id} value={a.id}>
                            {a.name} ({formatRupiah(a.balance)})
                        </option>
                    ))}
                </select>
            </div>

            <div>
                <label htmlFor="tx-date" className={labelClass}>
                    Date
                </label>
                <input
                    id="tx-date"
                    type="date"
                    value={values.date}
                    onChange={(e) => onChange("date", e.target.value)}
                    className={inputClass}
                    required
                />
            </div>
        </div>
    );
}

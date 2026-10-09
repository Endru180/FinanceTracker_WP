import { formatRupiah } from "../utils/format";
import { cssColor } from "../utils/theme";

const CX = 120;
const CY = 120;
const R = 100;
const STROKE = 18;

// Point on the arc: t = 0 is the left end, t = 1 is the right end
function pointAt(t) {
    const angle = Math.PI * (1 - t);
    return { x: CX + R * Math.cos(angle), y: CY - R * Math.sin(angle) };
}

export default function GaugeChart({ spent, goal, onEditGoal }) {
    const hasGoal = goal > 0;
    const ratio = hasGoal ? Math.min(spent / goal, 1) : 0;
    const remaining = goal - spent;
    const over = hasGoal && remaining < 0;
    const end = pointAt(ratio);
    const arcColor = over ? cssColor("danger") : cssColor("accent");

    return (
        <div className="rounded-md border border-border bg-surface px-6 py-8">
            <svg
                viewBox="0 0 240 140"
                className="mx-auto w-full max-w-xs"
                role="img"
                aria-label={
                    hasGoal
                        ? `${formatRupiah(Math.abs(remaining))} ${over ? "over" : "remaining"}`
                        : "No budget goal set"
                }
            >
                <path
                    d={`M ${CX - R} ${CY} A ${R} ${R} 0 0 1 ${CX + R} ${CY}`}
                    fill="none"
                    strokeWidth={STROKE}
                    strokeLinecap="round"
                    style={{ stroke: cssColor("surface-raised") }}
                />
                {ratio > 0 && (
                    <path
                        d={`M ${CX - R} ${CY} A ${R} ${R} 0 0 1 ${end.x} ${end.y}`}
                        fill="none"
                        strokeWidth={STROKE}
                        strokeLinecap="round"
                        style={{ stroke: arcColor }}
                    />
                )}
                <text
                    x={CX}
                    y={86}
                    textAnchor="middle"
                    fontSize="22"
                    fontWeight="600"
                    className="font-display"
                    style={{ fill: cssColor("ink") }}
                >
                    {hasGoal
                        ? formatRupiah(Math.abs(remaining))
                        : "No goal set"}
                </text>
                <text
                    x={CX}
                    y={108}
                    textAnchor="middle"
                    fontSize="12"
                    style={{ fill: cssColor("muted") }}
                >
                    {hasGoal
                        ? over
                            ? "Over budget"
                            : "Remaining"
                        : "Tap Goal to set one"}
                </text>
            </svg>

            <button
                type="button"
                onClick={onEditGoal}
                className="mx-auto mt-4 block rounded-full bg-surface-raised px-10 py-3 font-display text-sm font-medium text-ink transition-colors hover:text-accent"
            >
                Goal <span className="text-accent">{formatRupiah(goal)}</span>
            </button>
            <p className="mt-3 text-center text-xs text-muted">
                {formatRupiah(spent)} spent this month
            </p>
        </div>
    );
}

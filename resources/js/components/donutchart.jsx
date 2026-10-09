import { cssColor } from "../utils/theme";

// Colors for the top three categories, and for "everything else"
export const CHART_COLORS = [
    cssColor("accent"),
    cssColor("ink"),
    cssColor("muted"),
];
export const OTHER_COLOR = cssColor("faint");

export default function DonutChart({ segments, size = 150, thickness = 20 }) {
    const center = size / 2;
    const radius = (size - thickness) / 2;
    const circumference = 2 * Math.PI * radius;
    const total = segments.reduce((sum, s) => sum + s.value, 0);
    let offset = 0;

    return (
        <svg
            width={size}
            height={size}
            viewBox={`0 0 ${size} ${size}`}
            className="shrink-0"
            role="img"
            aria-label="Spending today by category"
        >
            <g transform={`rotate(-90 ${center} ${center})`}>
                <circle
                    cx={center}
                    cy={center}
                    r={radius}
                    fill="none"
                    strokeWidth={thickness}
                    style={{ stroke: cssColor("surface-raised") }}
                />
                {total > 0 &&
                    segments.map((segment, i) => {
                        const length = (segment.value / total) * circumference;
                        const circle = (
                            <circle
                                key={i}
                                cx={center}
                                cy={center}
                                r={radius}
                                fill="none"
                                strokeWidth={thickness}
                                strokeDasharray={`${length} ${circumference - length}`}
                                strokeDashoffset={-offset}
                                style={{ stroke: segment.color }}
                            />
                        );
                        offset += length;
                        return circle;
                    })}
            </g>
        </svg>
    );
}

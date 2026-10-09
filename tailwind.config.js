/** @type {import('tailwindcss').Config} */
const color = (name) => `rgb(var(--${name}) / <alpha-value>)`;

export default {
    content: ["./resources/**/*.blade.php", "./resources/**/*.jsx"],
    theme: {
        extend: {
            colors: {
                bg: color("bg"),
                surface: color("surface"),
                "surface-raised": color("surface-raised"),
                border: color("border"),
                ink: color("ink"),
                muted: color("muted"),
                accent: color("accent"),
                danger: color("danger"),
            },
            fontFamily: {
                display: ["Space Grotesk", "sans-serif"],
                body: ["IBM Plex Sans", "sans-serif"],
            },
        },
    },
    plugins: [],
};

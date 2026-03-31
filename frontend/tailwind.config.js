/** @type {import('tailwindcss').Config} */
export default {
    content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: "#f0f4ff",
                    100: "#e0e9ff",
                    400: "#6b8cff",
                    500: "#4f6ef7",
                    600: "#3d5be0",
                    900: "#1a2460",
                },
            },
            backdropBlur: {
                xs: "2px",
            },
        },
    },
    plugins: [],
};

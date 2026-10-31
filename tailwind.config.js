const defaultTheme = require("tailwindcss/defaultTheme");
const forms = require("@tailwindcss/forms");
const autoprefixer = require("autoprefixer");

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./storage/framework/views/*.php",
    "./resources/views/**/*.blade.php",
    "./resources/css/*.css",
    "./resources/js/*.js",
    "./resources/js/**/*.js",
    "./src/**/*.{html,js}"
  ],
  darkMode: "class",
  theme: {
    extend: {
      fontFamily: {
        sans: ["Figtree", ...defaultTheme.fontFamily.sans], // Makes Figtree the default font
        outfit: ["Outfit", "sans-serif"], // Optional font (use with `font-outfit`)
        figtree: ["Figtree", "sans-serif"],

      },
      screens: {
        "2xsm": "375px",
        xsm: "425px",
        "3xl": "2000px",
        ...defaultTheme.screens,
      },
      fontSize: {
        "title-2xl": ["72px", "90px"],
        "title-xl": ["60px", "72px"],
        "title-lg": ["48px", "60px"],
        "title-md": ["36px", "44px"],
        "title-sm": ["30px", "38px"],
        "theme-xl": ["20px", "30px"],
        "theme-sm": ["14px", "20px"],
        "theme-xs": ["12px", "18px"],
      },
      colors: {
        current: "currentColor",
        transparent: "transparent",
        white: "#FFFFFF",
        black: "#101828",
        "gray-dark": "#1a1a1a",
        success: {
          50: "#d1fae5", // Light success color
          100: "#a7f3d0",
          200: "#6ee7b7",
        },
        error: {
          50: "#fee2e2", // Light error color
          100: "#fecaca",
          200: "#fca5a5",
        },
        orange: {
          50: "#fff7ed",
          500: "#f97316", // Add more shades if needed
        },
        brand: {
          50: "#f5faff",
          500: "#1e40af",
        },
        orange: {
          50: "#FFF7ED",
          100: "#FFEDD5",
          200: "#FED7AA",
          300: "#FDBA74",
          400: "#FB923C",
          500: "#F97316", // This is bg-orange-500
          600: "#EA580C",
          700: "#C2410C",
          800: "#9A3412",
          900: "#7C2D12",
        },
        success: {
          50: "#ecfdf5",
          100: "#d1fae5",
          200: "#a7f3d0",
          300: "#6ee7b7",
          400: "#34d399",
          500: "#10b981", // Define success-500
          600: "#059669",
          700: "#047857",
          800: "#065f46",
          900: "#064e3b",
        },
        error: {
          50: "#fef2f2",
          100: "#fee2e2",
          200: "#fecaca",
          300: "#fca5a5",
          400: "#f87171",
          500: "#ef4444", // Define error-500
          600: "#dc2626",
          700: "#b91c1c",
          800: "#991b1b",
          900: "#7f1d1d",
        },
        brand: {
          25: "#F2F7FF",
          50: "#ECF3FF",
          100: "#DDE9FF",
          200: "#C2D6FF",
          300: "#9CB9FF",
          400: "#7592FF",
          500: "#465FFF",
          600: "#3641F5",
          700: "#2A31D8",
          800: "#252DAE",
          900: "#262E89",
          950: "#161950",
        },
        "blue-light": {
          25: "#F5FBFF",
          50: "#F0F9FF",
          100: "#E0F2FE",
          200: "#B9E6FE",
          300: "#7CD4FD",
          400: "#36BFFA",
          500: "#0BA5EC",
          600: "#0086C9",
          700: "#026AA2",
          800: "#065986",
          900: "#0B4A6F",
          950: "#062C41",
        },
      },
      boxShadow: {
        "slider-navigation": "0 4px 6px rgba(0, 0, 0, 0.1)",
        "datepicker": "0 4px 10px rgba(0, 0, 0, 0.15)",
        "theme-sm": "0 1px 2px rgba(0, 0, 0, 0.05)",
        "theme-xl": "0 10px 30px rgba(0, 0, 0, 0.3)",
        "theme-md": "0px 4px 8px -2px rgba(16, 24, 40, 0.10), 0px 2px 4px -2px rgba(16, 24, 40, 0.06)",
        "theme-lg": "0px 12px 16px -4px rgba(16, 24, 40, 0.08), 0px 4px 6px -2px rgba(16, 24, 40, 0.03)",
        "theme-xs": "0px 1px 2px 0px rgba(16, 24, 40, 0.05)",
      },
      zIndex: {
        1: "1",
        999999: "999999",
        99999: "99999",
        9999: "9999",
        999: "999",
      },
      spacing: {
        4.5: "1.125rem",
        5.5: "1.375rem",
        6.5: "1.625rem",
        7.5: "1.875rem",
      },
    },
  },
  plugins: [forms, autoprefixer],
};
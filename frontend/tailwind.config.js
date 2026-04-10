/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'seid-blue': '#005B96',
        'seid-dark': '#003D6B',
        'seid-light': '#E3F2FD',
      },
    },
  },
  plugins: [],
}
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/*.php",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Nunito','Inter', 'sans-serif'],
      },
      colors: {
        'primary': "#3B82F6"
      }
    },
  },
  plugins: [],
}
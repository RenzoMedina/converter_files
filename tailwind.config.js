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
  safelist: [
      'alert-error',
      'alert-success',
      'alert-info',
      'bg-red-50',
      'border-red-500',
      'text-red-800',
      'bg-green-50',
      'border-green-500',
      'text-green-800'
  ]
}
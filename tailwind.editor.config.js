/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'none',
  content: [
    './resources/assets/editor/**/*.{ts,vue,js}'
  ],
  theme: {
    extend: {
      colors: {
        primary: "#0041ff",
      },
      animation: {
        'fade-in': 'fadeIn .25s ease-out',
        'fade-out': 'fadeOut .25s ease-out',
      }
    },
  },
  plugins: [
    require('@tailwindcss/typography')
  ]
}

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'none',
  content: [
    './resources/assets/editor/**/*.{ts,vue,js}'
  ],
  theme: {
    extend: {
      animation: {
        'fade-in': 'fadeIn .25s ease-out',
      }
    },
  }
}

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/theme/**/*',
    './resources/assets/shop/ts/**/*'
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/typography')
  ]
}

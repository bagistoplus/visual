import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss'

export default defineConfig({
  plugins: [laravel({
    input: ['resources/css/shop.css', 'resources/ts/shop/index.ts']
  })],
  css: {
    postcss: {
      plugins: [
        tailwindcss({
          config: './tailwind.shop.config.js'
        })
      ]
    }
  }
});

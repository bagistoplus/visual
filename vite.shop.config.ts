import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/assets/shop/css/shop.css', 'resources/assets/shop/ts/index.ts'],
      buildDirectory: 'vendor/bagistoplus/visual/shop',
      hotFile: 'public/vendor/bagistoplus/visual/shop.hot',
    }),
  ],
  css: {
    postcss: {
      plugins: [
        tailwindcss({
          config: './tailwind.shop.config.js',
        }),
      ],
    },
  },
});

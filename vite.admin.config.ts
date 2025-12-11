import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    tailwindcss(),

    laravel({
      input: ['resources/assets/admin/css/admin.css', 'resources/assets/admin/ts/index.ts'],
      buildDirectory: 'vendor/bagistoplus/visual/admin',
      hotFile: 'public/vendor/bagistoplus/visual/admin.hot',
    }),
  ],
  base: '/vendor/bagistoplus/visual/admin/',
});

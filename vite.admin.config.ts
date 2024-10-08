import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss'

export default defineConfig({
  plugins: [laravel({
    input: [
      'resources/assets/admin/css/admin.css',
      'resources/assets/admin/ts/index.ts'
    ],
    buildDirectory: 'admin',
    hotFile: 'public/admin.hot'
  })],
  css: {
    postcss: {
      plugins: [
        tailwindcss({
        config: './tailwind.admin.config.js'
        })
      ]
    }
  }
});

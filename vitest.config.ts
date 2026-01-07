import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import AutoImport from 'unplugin-auto-import/vite';
import { fileURLToPath } from 'node:url';

export default defineConfig({
  plugins: [
    vue(),
    AutoImport({
      imports: ['vue', '@vueuse/core'],
      dts: false,
    }),
  ],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./resources/assets/editor/__tests__/setup.ts'],
    include: ['resources/assets/editor/__tests__/**/*.test.ts'],
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/assets/editor', import.meta.url)),
    },
  },
});

import path from 'node:path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss';
import Vue from '@vitejs/plugin-vue';
import VueMacros from 'unplugin-vue-macros/vite';
import VueRouter from 'unplugin-vue-router/vite';
import AutoImport from 'unplugin-auto-import/vite';
import { VueRouterAutoImports } from 'unplugin-vue-router';
import Components from 'unplugin-vue-components/vite';
import IconsResolver from 'unplugin-icons/resolver';
import Icons from 'unplugin-icons/vite';

export default defineConfig({
  resolve: {
    alias: {
      '~/': `${path.resolve(__dirname, 'resources/assets/editor')}/`,
    },
  },
  plugins: [
    laravel({
      input: ['resources/assets/editor/index.ts', 'resources/assets/editor/injected.ts'],
      buildDirectory: 'vendor/bagistoplus/visual/editor',
      hotFile: 'public/vendor/bagistoplus/visual/editor.hot',
    }),

    VueMacros({
      defineOptions: false,
      defineModels: false,
      plugins: {
        vue: Vue({
          script: {
            propsDestructure: true,
            defineModel: true,
          },
        }),
      },
    }),

    // https://github.com/posva/unplugin-vue-router
    VueRouter({
      dts: 'resources/assets/editor/typed-routes.d.ts',
      routesFolder: [
        {
          src: 'resources/assets/editor/views',
        },
      ],
      // importMode: 'sync',
    }),

    // https://github.com/antfu/unplugin-auto-import
    AutoImport({
      imports: [
        'vue',
        '@vueuse/core',
        VueRouterAutoImports,
        {
          // add any other imports you were relying on
          'vue-router/auto': ['useLink'],
        },
      ],
      dts: './resources/assets/editor/auto-imports.d.ts',
      dirs: ['./resources/assets/editor/composables'],
      vueTemplate: true,
      viteOptimizeDeps: true,
    }),

    // https://github.com/antfu/vite-plugin-components
    Components({
      dts: './resources/assets/editor/components.d.ts',
      dirs: ['resources/assets/editor/components'],
      resolvers: [IconsResolver()],
    }),

    Icons({
      compiler: 'vue3',
      autoInstall: true,
    }),
  ],
  css: {
    postcss: {
      plugins: [
        tailwindcss({
          config: './tailwind.editor.config.js',
        }),
      ],
    },
  },
});

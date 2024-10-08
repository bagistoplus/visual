import path from 'node:path'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss'
import Vue from '@vitejs/plugin-vue'
import VueMacros from 'unplugin-vue-macros/vite'
import VueRouter from 'unplugin-vue-router/vite'
import AutoImport from 'unplugin-auto-import/vite'
import { VueRouterAutoImports } from 'unplugin-vue-router'
import Components from 'unplugin-vue-components/vite'
import {PrimeVueResolver} from '@primevue/auto-import-resolver';

export default defineConfig({
  resolve: {
    alias: {
      '~/': `${path.resolve(__dirname, 'resources/ts/editor')}/`,
    },
  },
  plugins: [
    laravel({
      input: ['resources/ts/editor/index.ts', 'resources/ts/editor/injected.ts'],
      buildDirectory: 'editor',
      hotFile: 'public/editor.hot'
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
      dts: 'resources/ts/editor/typed-routes.d.ts',
      routesFolder: [
        {
          src: 'resources/ts/editor/views'
        }
      ]
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
      dts: './resources/ts/editor/auto-imports.d.ts',
      dirs: [
        './resources/ts/editor/composables',
      ],
      vueTemplate: true,
      viteOptimizeDeps: true,
    }),

    // https://github.com/antfu/vite-plugin-components
    Components({
      dts: './resources/ts/editor/components.d.ts',
      dirs: ['resources/ts/editor/components'],
      resolvers: [
        PrimeVueResolver(),
        (componentName) => {
          if (componentName.endsWith('Icon')) {
            return {name: componentName, from: '@heroicons/vue/24/outline'}
          }
        }
      ]
    }),
  ],
  css: {
    postcss: {
      plugins: [
        tailwindcss({
          config: './tailwind.editor.config.js'
        })
      ]
    }
  }
});

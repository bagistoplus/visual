import type { App as VueApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import { routes, handleHotUpdate } from 'vue-router/auto-routes';
import { createI18n } from 'vue-i18n';

import type { ThemeEditorConfig } from './types';

type BootingCallback = (ctx: { vueApp: any }) => void;

export default class ThemeEditor {
  config: ThemeEditorConfig;
  bootingCallbacks: BootingCallback[] = [];
  vueApp: VueApp;

  constructor(config: ThemeEditorConfig, vueApp: VueApp) {
    this.config = config;
    this.vueApp = vueApp;
  }

  channels() {
    return this.config.channels;
  }

  storefrontUrl() {
    return this.config.storefrontUrl;
  }

  availableSections() {
    return this.config.sections;
  }

  route(name: string) {
    return this.config.routes[name as keyof typeof this.config.routes];
  }

  /**
   * Register a callback to be called the theme editor start.
   * This typically used when extending the theme editor with
   * with custom setting types to register vue components
   */
  booting(callback: BootingCallback) {
    this.bootingCallbacks.push(callback);
  }

  boot() {
    this.setup();

    this.bootingCallbacks.forEach((cb) => cb({ vueApp: this.vueApp }));
    document.dispatchEvent(new CustomEvent('visual:editor:booting', { detail: { vueApp: this.vueApp } }));

    this.vueApp.mount('#app');
  }

  setup() {
    const pinia = createPinia();
    const router = createRouter({
      routes,
      history: createWebHistory(this.config.baseUrl),
    });

    const i18n = createI18n({
      locale: this.config.editorLocale,
      messages: {
        [this.config.editorLocale]: this.config.messages,
      },
    });

    this.vueApp.use(pinia);
    this.vueApp.use(router);
    this.vueApp.use(i18n);
  }
}

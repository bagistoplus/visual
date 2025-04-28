import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import { routes, handleHotUpdate } from 'vue-router/auto-routes';
import { createI18n } from 'vue-i18n';

import App from '../../assets/editor/App.vue';
import './index.css';

const app = createApp(App);
const pinia = createPinia();
const router = createRouter({
  routes,
  history: createWebHistory(window.ThemeEditor.baseUrl),
});

const i18n = createI18n({
  locale: window.ThemeEditor.editorLocale,
  messages: {
    [window.ThemeEditor.editorLocale]: window.ThemeEditor.messages,
  },
});

app.use(pinia);
app.use(router);
app.use(i18n);

app.mount('#app');

if (import.meta.hot) {
  handleHotUpdate(router);
}

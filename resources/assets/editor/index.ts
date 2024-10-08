import PrimeVue from "primevue/config";
import { createApp } from "vue";
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from "vue-router";
import { routes } from 'vue-router/auto-routes';

import Aura from './presets/aura'
import App from '../../assets/editor/App.vue';
import './index.css';

const app = createApp(App);
const pinia = createPinia()
const router = createRouter({
  routes,
  history: createWebHistory(window.ThemeEditor.baseUrl),
})

app.use(pinia)
app.use(router);
app.use(PrimeVue, { unstyled: true, pt: Aura });

app.mount('#app')

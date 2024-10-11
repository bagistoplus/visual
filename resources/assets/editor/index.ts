import { createApp } from "vue";
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from "vue-router";
import { routes, handleHotUpdate } from 'vue-router/auto-routes';

import App from '../../assets/editor/App.vue';
import './index.css';

import {NumberInputControl} from '@ark-ui/vue/number-input';

const app = createApp(App);
const pinia = createPinia()
const router = createRouter({
  routes,
  history: createWebHistory(window.ThemeEditor.baseUrl),
})

app.use(pinia)
app.use(router);

app.mount('#app')

if (import.meta.hot) {
  handleHotUpdate(router)
}

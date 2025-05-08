import * as Vue from 'vue';
import { createApp } from 'vue';

import App from '../../assets/editor/App.vue';
import './index.css';
import ThemeEditor from './ThemeEditor';

const editorConfig = window.editorConfig;
const app = createApp(App);

document.addEventListener('DOMContentLoaded', () => {
  window.ThemeEditor.boot();
});

window.ThemeEditor = new ThemeEditor(editorConfig, app);
(window as any).Vue = Vue;

// if (import.meta.hot) {
//   handleHotUpdate(router);
// }

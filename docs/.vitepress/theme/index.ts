import DefaultTheme from 'vitepress/theme';

import { Tab, Tabs } from 'vue3-tabs-component';
import '@red-asuka/vitepress-plugin-tabs/dist/style.css';
import './custom.css';
import SettingPreview from './components/SettingPreview.vue';
import Layout from './Layout.vue';

export default {
  extends: DefaultTheme,
  Layout,
  enhanceApp({ app }) {
    app.component('Tab', Tab);
    app.component('Tabs', Tabs);

    app.component('SettingPreview', SettingPreview);
  },
};

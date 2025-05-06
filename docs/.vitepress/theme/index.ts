import DefaultTheme from 'vitepress/theme';

import { Tab, Tabs } from 'vue3-tabs-component';
import '@red-asuka/vitepress-plugin-tabs/dist/style.css';
import './custom.css';
import SettingPreview from './components/SettingPreview.vue';

export default {
  extends: DefaultTheme,
  enhanceApp({ app }) {
    app.component('Tab', Tab);
    app.component('Tabs', Tabs);

    app.component('SettingPreview', SettingPreview);
  },
};

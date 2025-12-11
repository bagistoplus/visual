import DefaultTheme from 'vitepress/theme';
import { h } from 'vue';

import { Tab, Tabs } from 'vue3-tabs-component';
import '@red-asuka/vitepress-plugin-tabs/dist/style.css';
import './custom.css';
import SettingPreview from './components/SettingPreview.vue';
import CustomLayout from './Layout.vue';
import AnnouncementBanner from './components/AnnouncementBanner.vue';

export default {
  extends: DefaultTheme,
  Layout() {
    return h(CustomLayout, null, {
      'layout-top': () => h(AnnouncementBanner),
    });
  },
  enhanceApp({ app }) {
    app.component('Tab', Tab);
    app.component('Tabs', Tabs);

    app.component('SettingPreview', SettingPreview);
  },
};

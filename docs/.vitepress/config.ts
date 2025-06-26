import { defineConfig } from 'vitepress';
import tabsPlugin from '@red-asuka/vitepress-plugin-tabs';
import llmstxt from 'vitepress-plugin-llms';

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: 'Bagisto Visual',
  description: 'A theme framework and a visual theme editor for Bagisto e-commerce framework',
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    search: {
      provider: 'local',
    },
    nav: [
      { text: 'Guide', link: '/introduction/getting-started' },
      { text: 'Theme Editor', link: '/theme-editor/overview' },
      { text: 'Demo', link: 'https://visual-debut-demo.bagistoplus.com' },
      { text: 'Sections Pack', link: 'https://bagistosectionspro.com/?ref=doc' },
      // { text: 'GitHub', link: 'https://github.com/bagistoplus/visual' },
    ],

    sidebar: {
      '/': [
        {
          text: 'Introduction',
          items: [
            { text: 'What is Bagisto Visual?', link: '/introduction/what-is-bagisto-visual' },
            { text: 'Features and Benefits', link: '/introduction/features-benefits' },
            { text: 'Getting Started', link: '/introduction/getting-started' },
            { text: 'LLMs.txt', link: '/introduction/llms' },
          ],
        },
        {
          text: 'Core Concepts',
          items: [
            { text: 'Architecture', link: '/core-concepts/architecture' },
            { text: 'Layouts', link: '/core-concepts/layouts' },
            {
              text: 'Templates',
              collapsed: true,
              items: [
                { text: 'Overview', link: '/core-concepts/templates/overview' },
                { text: 'JSON Template', link: '/core-concepts/templates/json-template' },
                { text: 'Available templates', link: '/core-concepts/templates/available' },
              ],
            },
            { text: 'Sections', link: '/core-concepts/sections' },
            {
              text: 'Settings',
              collapsed: true,
              items: [
                { text: 'Overview', link: '/core-concepts/settings/overview' },
                { text: 'Setting types', link: '/core-concepts/settings/types' },
                { text: 'Theme settings', link: '/core-concepts/settings/theme-settings' },
              ],
            },
          ],
        },
        {
          text: 'Building a Theme',
          items: [
            { text: 'Creating a Theme', link: '/building-theme/create-theme' },
            { text: 'Adding Layouts', link: '/building-theme/adding-layouts' },
            { text: 'Adding Templates', link: '/building-theme/adding-templates' },
            {
              text: 'Adding Sections',
              collapsed: true,
              items: [
                { text: 'Overview', link: '/building-theme/adding-sections/overview' },
                { text: 'Creating a section', link: '/building-theme/adding-sections/creating-section' },
                { text: 'Section attributes', link: '/building-theme/adding-sections/section-attributes' },
                {
                  text: 'Defining settings and blocks',
                  link: '/building-theme/adding-sections/defining-section-schema',
                },
                { text: 'Writing the section view', link: '/building-theme/adding-sections/writing-section-view' },
                {
                  text: 'Using section in templates',
                  link: '/building-theme/adding-sections/using-section',
                },
                { text: 'Integrating with the editor', link: '/building-theme/adding-sections/integrating-editor' },
              ],
            },
            {
              text: 'Best practices',
              collapsed: true,
              items: [
                { text: 'Overview', link: '/building-theme/best-practices/overview' },
                { text: 'Styling and Color System', link: '/building-theme/best-practices/styling' },
                { text: 'Accessibility', link: '/building-theme/best-practices/accessibility' },
                { text: 'Performance', link: '/building-theme/best-practices/performance' },
              ],
            },
          ],
        },
        {
          text: 'Theme Editor',
          items: [
            { text: 'Overview', link: '/theme-editor/overview' },
            { text: 'Interface Guide', link: '/theme-editor/interface-guide' },
            { text: 'Advanced Usage', link: '/theme-editor/advanced-usage' },
          ],
        },
        {
          text: 'Advanced Guides',
          items: [
            { text: 'Extending Functionality', link: '/advanced-guides/extending' },
            { text: 'Debugging and Troubleshooting', link: '/advanced-guides/debugging' },
          ],
        },
        // {
        //   text: 'Contribution Guidelines',
        //   link: '/contribution-guidelines',
        // },
      ],
    },

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2025 BagistoPlus',
    },

    socialLinks: [{ icon: 'github', link: 'https://github.com/bagistoplus/visual' }],
  },

  lastUpdated: true,

  markdown: {
    config: (md) => {
      tabsPlugin(md);
    },
  },

  vite: {
    plugins: [llmstxt() as any],
  },

  head: [
    ...(process.env.NODE_ENV === 'production'
      ? [
          [
            'script',
            {
              defer: 'true',
              src: 'https://cloud.umami.is/script.js',
              'data-website-id': 'b89cd703-13ec-41b3-b7eb-9f89aa3cf710',
            },
          ] as [string, Record<string, string>],
        ]
      : []),
  ],

  sitemap: {
    hostname: 'https://visual.bagistoplus.com',
  },
});

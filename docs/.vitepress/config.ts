import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Bagisto Visual",
  description: "A theme framework and a visual theme editor for Bagisto e-commerce framework",
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    nav: [
      { text: 'Guide', link: '/guide/' },
      { text: 'Theme Editor', link: '/theme-editor/' },
      { text: 'GitHub', link: 'https://github.com/bagistoplus/visual' }
    ],

    sidebar: {
      '/': [
        {
          text: 'Introduction',
          items: [
            { text: 'What is Bagisto Visual?', link: '/introduction/what-is-bagisto-visual' },
            { text: 'Features and Benefits', link: '/introduction/features-benefits' },
            { text: 'Getting Started', link: '/introduction/getting-started' },
          ]
        },
        {
          text: 'Core Concepts',
          items: [
            { text: 'Sections and Templates', link: '/core-concepts/sections-templates' },
            { text: 'JSON Templates Overview', link: '/core-concepts/json-templates-overview' },
            { text: 'Folder/File Structure', link: '/core-concepts/folder-file-structure' }
          ]
        },
        {
          text: 'Using Bagisto Visual',
          items: [
            { text: 'Creating a Theme', link: '/using-bagisto-visual/creating-theme' },
            { text: 'Adding Sections', link: '/using-bagisto-visual/adding-sections' },
            { text: 'Customizing Templates', link: '/using-bagisto-visual/customizing-templates' },
            { text: 'Managing Assets', link: '/using-bagisto-visual/managing-assets' }
          ]
        },
        {
          text: 'Theme Editor',
          items: [
            { text: 'Overview', link: '/theme-editor/overview' },
            { text: 'Interface Guide', link: '/theme-editor/interface-guide' },
            { text: 'Advanced Usage', link: '/theme-editor/advanced-usage' }
          ]
        },
        {
          text: 'Advanced Guides',
          items: [
            { text: 'Extending Functionality', link: '/advanced-guides/extending-functionality' },
            { text: 'Debugging and Troubleshooting', link: '/advanced-guides/debugging-troubleshooting' }
          ]
        },
        {
          text: 'Contribution Guidelines',
          link: '/contribution-guidelines'
        }
      ]
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/vuejs/vitepress' }
    ]
  }
})

import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "JsonShape",
  description: "Typed objects for your database JSON columns in Laravel.",
  // Served from https://plumthedev.github.io/json-shape/ — assets must resolve
  // under that sub-path, not the domain root.
  base: "/json-shape/",
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    nav: [
      { text: 'Quick Start', link: '/#quick-start' },
      { text: 'Docs', link: '/usage' }
    ],

    sidebar: {
      '/usage': [
        {
          text: 'Usage',
          items: [
            { text: 'Overview', link: '/usage' },
            { text: 'The example shape', link: '/usage/the-example-shape' },
            { text: 'Reading properties', link: '/usage/reading-properties' },
            { text: 'Setting properties', link: '/usage/setting-properties' },
            { text: 'Eloquent support', link: '/usage/eloquent-support' },
            { text: 'Common tools', link: '/usage/common-tools' }
          ]
        }
      ]
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/plumthedev/json-shape' }
    ],

    editLink: {
      pattern: 'https://github.com/plumthedev/json-shape/edit/main/docs/:path'
    }
  }
})

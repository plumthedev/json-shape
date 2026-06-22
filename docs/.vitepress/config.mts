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
          text: 'Usage guide',
          items: [
            { text: 'Overview', link: '/usage' },
            { text: '1. Define a shape', link: '/usage/defining-a-shape' },
            { text: '2. Read values', link: '/usage/reading-values' },
            { text: '3. Write values', link: '/usage/writing-values' },
            { text: '4. Cast it on a model', link: '/usage/eloquent-casting' },
            { text: '5. Create & combine shapes', link: '/usage/creating-shapes' },
            { text: '6. Type safety in depth', link: '/usage/type-safety' },
            { text: '7. Helpers, macros & errors', link: '/usage/helpers' }
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

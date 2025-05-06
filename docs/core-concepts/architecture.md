# Anatomy of a Theme

Bagisto Visual brings a **modern, flexible theme system** to Bagisto — **heavily inspired by Shopify's proven architecture**.

At its core, a theme is a **structured collection of layouts, templates, sections, blocks, and settings**, allowing both developers and store owners to collaboratively build and manage storefronts with ease.

By adopting a system modeled after Shopify, Bagisto Visual empowers **merchants** and **theme developers** to take full control of the storefront experience — crafting **beautiful, fully customized online stores** that stand out from the competition.

This section explains **how a Bagisto Visual theme is organized**, **how the core parts fit together**, and **how developers and merchants can collaborate** to build dynamic, customizable storefronts.

![Theme Architecture Overview](/theme-anatomy-4.png)

## Theme Structure

A theme is made up of the following main parts:

| Number | Part                 | Description                                                                                                                                                                                                                |
| ------ | -------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1      | **Layout**           | The base structure of the page. Defines global elements like header, footer, and meta.                                                                                                                                     |
| 2      | **Template**         | Define what to render and where. Each page of the storefront has its own associated template. Templates can be Blade (with dynamic logic) or JSON/YAML (as wrappers for sections). Only one template is rendered per page. |
| 3,4    | **Layout section**   | Content blocks that are static in the layout. shared accross all pages                                                                                                                                                     |
| 5      | **Template section** | Modular, reusable content blocks used inside templates.                                                                                                                                                                    |
| 6      | **Block**            | smaller repeatable components inside sections.                                                                                                                                                                             |

> **Note:**
> Templates define the structure of different page types in your storefront, such as the homepage, product pages, category pages, cart, and more. Each page is rendered based on its associated template, making it easy to create custom layouts for different parts of your store.

## Folder Structure

```plaintext
/theme/
├── resources/
│   ├── views/
│       ├── components/     # Custom Blade or Livewire components
│       ├── layouts/        # Layouts (default layout is mandatory)
│       ├── sections/       # Section Blade files
│       ├── templates/      # Page templates (JSON, YAML, or Blade)
├── src/
│   ├── Sections/           # PHP classes for custom sections
```

## Key Concepts

### Layouts

- Define the **global structure** of your pages.
- Must include a `default.blade.php` layout inside `/resources/views/layouts/`.
- Layouts typically contain the `<head>`, header, footer, and dynamic content area.

### Templates

- Represent **individual pages** (e.g., homepage, product page).
- Can be written in **Blade**, **JSON**, or **YAML**.
- Templates **list sections** in the desired order.
- Only one template is rendered at a time based on the page being viewed.

### Sections

- **Reusable content blocks** like banners, product grids, and footers.
- Created using Blade templates located in `/resources/views/sections/`.

### Blocks (Component)

- **Sub-components** inside sections.
- Allow merchants to add, reorder, or remove content inside a section.

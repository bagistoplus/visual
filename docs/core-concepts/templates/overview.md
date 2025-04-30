# Templates

Templates control **the structure and layout of individual pages** in a Bagisto Visual theme.

Each storefront page — such as the homepage, a product page, a cart page, or a category listing — is linked to a **template** that defines **what content to render** and **how to organize it**.

Templates act as **blueprints** for pages by specifying:

- Which **sections** appear.
- The **order** they appear in.
- How the page content is assembled.

## Key Characteristics

- **Each page type has an associated template.**
  Templates define how the content of that page is structured.

- **Templates use Blade, JSON (or YAML) formats.**
  Developers can choose between:

  - **Blade templates** (`.blade.php`) for dynamic, code-driven pages.
  - **JSON or YAML templates** (`.json`, `.yaml`) for lightweight, section-driven pages.

- **Only one template is rendered per page.**
  When a user navigates to a page, Bagisto Visual selects and renders the corresponding template.

- **Templates organize sections, not content directly.**
  Sections provide the actual content and functionality.

## Location of Templates

Templates are stored inside the following directory:

```plaintext
/theme/
└── resources/
    └── views/
        └── templates/
```

Example:

```plaintext
/templates/
├── index.blade.php       # Homepage template
├── product.json          # Product page
├── category.json         # Category page
├── cart.yaml             # Cart page
├── checkout.yaml         # Checkout page
├── page.yaml             # CMS pages
├── search.json           # Search results page
```

## Blade Templates vs JSON/YAML Templates

| Format        | Description                                                                                                                                                                | When to Use                                                                        |
| ------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| **Blade**     | Full Laravel Blade templates. Allow dynamic PHP, Livewire components, and advanced logic. **Blade templates cannot be customized or rearranged through the Theme Editor.** | Use Blade when you need maximum flexibility, dynamic behavior, or complex layouts. |
| **JSON/YAML** | Static templates that define a list of sections in order. **Sections can be added, removed, and reordered visually by merchants through the Theme Editor.**                | Use JSON/YAML for customizable, section-driven pages managed easily by merchants.  |

> **Tip:**
> JSON and YAML templates are ideal for pages where **merchant customization and flexibility** are important (like the homepage, landing pages, etc.).
> Blade templates are better suited for pages with **strict structure** or **advanced dynamic functionality**.

## Template Behavior

- **Blade templates** contain Blade code and manually control section rendering.
- **JSON/YAML templates** define sections declaratively. Merchants can easily reorder, add, or remove sections through the Theme Editor.

In both cases, **sections** are responsible for the actual content.

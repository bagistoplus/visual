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

- **Templates use Blade, JSON, YAML, or PHP formats.**
  Developers can choose between:

  - **Blade templates** (`.blade.php`) for dynamic, code-driven pages.
  - **JSON or YAML templates** (`.json`, `.yaml`) for lightweight, section-driven pages.
  - **PHP templates** (`.visual.php`) for programmatic, type-safe section configurations.

- **Only one template is rendered per page.**
  When a user navigates to a page, Bagisto Visual selects and renders the corresponding template.

- **Templates organize sections, not content directly.**
  Sections provide the actual content and functionality.

- **Templates are page-specific, regions are shared.**
  Unlike [regions](../regions.md) (which are shared across all pages), templates define content for individual page types.

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
├── index.blade.php       # Homepage template (Blade)
├── product.visual.php    # Product page (PHP)
├── category.json         # Category page (JSON)
├── cart.yaml             # Cart page (YAML)
├── checkout.yaml         # Checkout page (YAML)
├── page.yaml             # CMS pages (YAML)
├── search.json           # Search results page (JSON)
```

## Template Format Comparison

| Format        | Description                                                                                                                                                                | When to Use                                                                        |
| ------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| **Blade**     | Full Laravel Blade templates. Allow dynamic PHP, Livewire components, and advanced logic. **Blade templates cannot be customized or rearranged through the Theme Editor.** | Use Blade when you need maximum flexibility, dynamic behavior, or complex layouts. |
| **JSON/YAML** | Static templates that define a list of sections in order. **Sections can be added, removed, and reordered visually by merchants through the Theme Editor.**                | Use JSON/YAML for customizable, section-driven pages managed easily by merchants.  |
| **PHP** (`.visual.php`) | Programmatic templates using `TemplateBuilder` API. Provide IDE support, type safety, and PHP features. **Fully compatible with Theme Editor like JSON/YAML.**  | Use PHP templates for complex configurations, preset class usage, and developer-focused workflows with IDE benefits. |

> **Tip:**
> - **[JSON/YAML templates](./json-yaml.md)** are ideal for simple, declarative page structures where merchant customization is important.
> - **[PHP templates](./php-templates.md)** are best for complex templates where IDE support and type safety are valuable.
> - **Blade templates** are suited for pages with strict structure or advanced dynamic functionality.

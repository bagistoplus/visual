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

| Format                  | Description                                                                                                                                                                | When to Use                                                                                                          |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| **Blade**               | Full Laravel Blade templates. Allow dynamic PHP, Livewire components, and advanced logic. **Blade templates cannot be customized or rearranged through the Theme Editor.** | Use Blade when you need maximum flexibility, dynamic behavior, or complex layouts.                                   |
| **JSON/YAML**           | Static templates that define a list of sections in order. **Sections can be added, removed, and reordered visually by merchants through the Theme Editor.**                | Use JSON/YAML when you prefer simple text file syntax and direct editing.                                    |
| **PHP** (`.visual.php`) | Interchangeable with JSON/YAML using `TemplateBuilder` API. Provides IDE support, type safety, and PHP features. **Fully compatible with Theme Editor like JSON/YAML.**             | Use PHP templates when you want IDE autocomplete, type safety, and the ability to use PHP features like variables and loops. |

> [!TIP]
>
> - **[JSON/YAML templates](./json-yaml.md)** - Simple syntax, edit directly in text files
> - **[PHP templates](./php-templates.md)** - Same result, but with IDE autocomplete, type safety, and PHP features
> - Both JSON/YAML and PHP formats are fully interchangeable and produce identical results in the Theme Editor
> - **Blade templates** are suited for pages with strict structure or advanced dynamic functionality

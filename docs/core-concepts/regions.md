# Regions

Regions are **customizable zones shared across all templates** in your theme. They allow merchants to fully control shared areas like headers and footers through the Visual Editor—adding, removing, and rearranging sections without touching code.

## What Are Regions?

A **region** is a reusable template area that appears on multiple pages. Common examples include:

- **Header** - Site navigation, logo, search, cart preview
- **Footer** - Company info, links, newsletter signup
- **Announcement Bar** - Promotional messages
- **Sidebar** - Filters, widgets (for applicable layouts)

Unlike page-specific templates, regions are **shared across your entire store**. When a merchant customizes the header region, those changes appear on every page that includes the header.

## Why Regions Matter

### The Problem with v1 (Static Shared Sections)

In Visual v1, shared elements like headers and footers were **hardcoded in layout files**. Merchants couldn't:
- Add new elements to the header
- Remove unwanted navigation items
- Reorder sections in the footer
- Customize shared areas without developer help

**Example v1 limitation**: If a merchant wanted to add a promotional banner above the header, they needed a developer to modify the layout file.

### The v2 Solution (Customizable Regions)

Regions make shared areas **fully customizable** through the Visual Editor. Merchants can:
- ✅ Add new sections to the header or footer
- ✅ Remove or hide existing sections
- ✅ Reorder sections visually
- ✅ Configure section settings
- ✅ Create multiple header/footer variations

**Same example in v2**: Merchants can simply add an announcement bar section to the header region themselves—no code required.

## Regions vs Templates

| Aspect | **Regions** | **Templates** |
|--------|-------------|---------------|
| **Scope** | Shared across all pages | Specific to one page type |
| **Purpose** | Common elements (header, footer) | Page-specific content (homepage, product) |
| **Customization** | Merchants customize once, applies everywhere | Merchants customize per page type |
| **Location** | `resources/views/regions/` | `resources/views/templates/` |
| **Usage** | Included in layouts via `@visualRegion()` | Rendered automatically per page type |

## Creating Region Templates

Regions use the same template formats as pages: **JSON**, **YAML**, or **PHP**.

### Method 1: JSON Format

Create a region file in `resources/views/regions/`:

```json
// resources/views/regions/header.json
{
  "id": "header",
  "name": "Header",
  "blocks": {
    "announcement-bar": {
      "type": "@visual-debut/announcement-bar"
    },
    "main-header": {
      "type": "@visual-debut/header",
      "properties": {
        "content_width": "container"
      }
    }
  },
  "order": ["announcement-bar", "main-header"]
}
```

### Method 2: YAML Format

```yaml
# resources/views/regions/footer.yaml
id: footer
name: Footer

blocks:
  newsletter:
    type: '@visual-debut/newsletter'
    properties:
      heading: 'Stay Connected'

  main-footer:
    type: '@visual-debut/footer'
    properties:
      content_width: 'container'

order:
  - newsletter
  - main-footer
```

### Method 3: PHP Format

PHP templates provide IDE support and type safety:

```php
<?php
// resources/views/regions/header.visual.php

use BagistoPlus\Visual\Support\TemplateBuilder;
use BagistoPlus\Visual\Support\PresetBlock;

return TemplateBuilder::make()
    ->id('header')
    ->name('Header')

    ->section('announcement-bar', '@visual-debut/announcement-bar')

    ->section('main-header', '@visual-debut/header', fn($section) => $section
        ->properties([
            'content_width' => 'container',
        ])
        ->blocks([
            PresetBlock::make('@visual-debut/group')
                ->id('container-logo')
                ->name('Logo')
                ->blocks([
                    PresetBlock::make('@visual-debut/logo')->name('Logo'),
                ]),

            PresetBlock::make('@visual-debut/group')
                ->id('container-nav')
                ->name('Navigation')
                ->blocks([
                    PresetBlock::make('@visual-debut/header-nav')->name('Navigation'),
                ]),
        ])
    )

    ->order(['announcement-bar', 'main-header']);
```

## Required Properties

Every region template must define:

| Property | Description | Example |
|----------|-------------|---------|
| **`id`** | Unique identifier for the region | `"header"`, `"footer"` |
| **`name`** | Display name in Visual Editor | `"Site Header"`, `"Main Footer"` |
| **`blocks`** | Sections in the region | See examples above |
| **`order`** | Rendering order of sections | `["announcement-bar", "main-header"]` |

::: tip
The `id` is used in the `@visualRegion()` directive, while `name` appears in the Visual Editor interface for merchants.
:::

## Using Regions in Layouts

Include regions in your layout files using the `@visualRegion()` directive:

```blade
<!-- resources/views/layouts/default.blade.php -->
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('shop::partials.head')
</head>
<body>
    <main role="main">
        {{-- Header region (shared across all pages) --}}
        @visualRegion('header')

        {{-- Page-specific template content --}}
        @section('body')
            @visual_layout_content
        @show

        {{-- Footer region (shared across all pages) --}}
        @visualRegion('footer')
    </main>
</body>
</html>
```

The directive renders the customized region content that merchants configure in the Visual Editor.

## Merchant Customization Experience

When merchants edit their theme in the Visual Editor:

1. **Navigate to Theme Editor** → **Regions** section
2. **Select a region** (e.g., "Header")
3. **Add sections** from the section library
4. **Configure sections** using the settings panel
5. **Reorder sections** by dragging and dropping
6. **Remove sections** they don't need
7. **Save changes** — updates appear across all pages immediately

### Example Customization Flow

**Scenario**: Merchant wants to add a promotional banner above the header.

1. Open **Visual Editor** → **Regions** → **Header**
2. Click **"Add Section"**
3. Select **"Announcement Bar"** from the section library
4. Configure the text: `"Free shipping on orders over $50!"`
5. Drag the announcement bar above the main header
6. Click **Save**
7. ✅ Banner now appears on **every page** automatically

## Common Use Cases

### Header Region

Typical header elements merchants can customize:

- Logo and branding
- Navigation menu
- Search bar
- User account dropdown
- Shopping cart preview
- Language/currency selectors
- Promotional announcement bars

### Footer Region

Typical footer elements merchants can customize:

- Company information
- Link columns (About, Support, Legal)
- Newsletter signup
- Social media links
- Payment method badges
- Copyright notice

### Announcement Bar Region

A lightweight region for store-wide messages:

- Sales and promotions
- Shipping updates
- Holiday hours
- Important announcements

## Best Practices

### When to Use Regions

✅ **Use regions for:**
- Elements that appear on multiple pages (header, footer)
- Store-wide announcements
- Shared sidebars or toolbars
- Elements merchants should control globally

❌ **Don't use regions for:**
- Page-specific content (use templates instead)
- One-off elements on a single page
- Content that varies by page type

### Naming Conventions

- **IDs**: Use lowercase with hyphens: `header`, `main-footer`, `top-bar`
- **Names**: Use descriptive titles: `"Site Header"`, `"Main Footer"`, `"Top Announcement Bar"`

### Organization

Keep regions focused and purposeful:

```plaintext
resources/views/regions/
├── header.visual.php       # Main site header
├── footer.visual.php       # Main site footer
├── announcement.visual.php # Top announcement bar
└── sidebar.visual.php      # Optional sidebar (for layouts that use it)
```

### Performance Considerations

- Regions are cached and rendered efficiently
- Avoid excessive nesting of sections within regions
- Keep region sections focused on their specific purpose

## Related Concepts

- **[Templates](./templates/overview.md)** - Page-specific structures (homepage, product page, etc.)
- **[Sections](./sections/overview.md)** - Individual content blocks used in regions and templates
- **[PHP Templates](./templates/php-templates.md)** - Programmatic way to define regions with IDE support

## Comparison with Shopify

Regions in Bagisto Visual are similar to [Shopify's Section Groups](https://shopify.dev/docs/storefronts/themes/architecture/section-groups):

| Shopify | Bagisto Visual |
|---------|----------------|
| Section Groups | Regions |
| `{% sections 'header' %}` | `@visualRegion('header')` |
| `sections/header-group.json` | `regions/header.visual.php` |

Both concepts solve the same problem: **making shared layout areas customizable by merchants** without requiring code changes.

---

::: tip Next Steps
- Learn how to create [Templates](./templates/overview.md) for page-specific content
- Explore [Sections](./sections/overview.md) available for use in regions
- Review the [PHP Templates guide](./templates/php-templates.md) for IDE-supported region definitions
:::

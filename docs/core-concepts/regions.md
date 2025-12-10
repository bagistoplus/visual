# Regions

Regions are **customizable zones shared across all templates** in your theme. They allow merchants to fully control shared areas like headers and footers through the Visual Editor—adding, removing, and rearranging sections without touching code.

## What Are Regions?

Regions are layout zones where sections can be placed and rendered. Think of them as placeholders in your layouts that can contain one or more sections. Common examples include:

- **Header** - Site navigation, logo, search, cart preview
- **Footer** - Company info, links, newsletter signup

Unlike page-specific templates, regions are **shared across your entire store**. When a merchant customizes the header region, those changes appear on every page that includes the header.

## Why Regions Matter

### The Problem with v1 (Single Static Section)

In Visual v1, shared elements like headers and footers were **single sections** that merchants could customize in the Visual Editor. However:

- Merchants could only configure the **one header section** provided by the developer
- They couldn't **add additional sections** to the header/footer zones (e.g., announcement bar above header)
- They couldn't **remove or reorder** multiple sections in these zones
- Adding new elements required a developer to modify the header section code

**Example v1 limitation**: If a merchant wanted to add a promotional announcement bar above the header, they needed a developer to either add it to the existing header section code or create a custom solution.

### The v2 Solution (Customizable Regions)

Regions make shared areas **fully customizable** through the Visual Editor by allowing **multiple sections** in header/footer zones. Merchants can:

- ✅ Add new sections to the header or footer region (announcement bars, promotional banners, etc.)
- ✅ Remove or hide existing sections
- ✅ Reorder sections visually within the region
- ✅ Configure each section's settings independently
- ✅ Create multiple header/footer variations

**Same example in v2**: Merchants can simply add an announcement bar section to the header region themselves—no code required. The header region can contain multiple sections that merchants manage.

## Region Structure

Each region has the following structure:

```json
{
  "id": "header",              // Required unique identifier (used in @visualRegion('header'))
  "name": "Header",            // Required display name (shown in editor)
  "sections": {                // Sections in this region
    "announcement": {
      "type": "visual-announcement-bar",
      "settings": { ... }
    },
    "main-header": {
      "type": "visual-header",
      "settings": { ... },
      "blocks": { ... },
      "order": [ ... ]
    }
  },
  "order": ["announcement", "main-header"]  // Section render order
}
```

**Properties:**

| Property       | Required | Description                                                                                                                                                                                                                   | Example                           |
| -------------- | -------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------- |
| **`id`**       | Yes      | Unique identifier used in the `@visualRegion('id')` directive to render the region                                                                                                                                            | `"header"`, `"footer"`            |
| **`name`**     | Yes      | Display name shown in the Visual Editor                                                                                                                                                                                       | `"Site Header"`, `"Main Footer"`  |
| **`sections`** | No       | Object containing sections that belong to this region. Each section follows the same structure as sections in templates (with `type`, `settings`, `blocks`, and `order` properties). See [Templates](./templates/overview.md) | See examples below                |
| **`order`**    | No       | Array of section IDs defining render order. If not defined, sections are rendered in the order they appear in the `sections` object.                                                                                          | `["announcement", "main-header"]` |

## Defining Regions

Regions use the same formats as templates: **JSON**, **YAML**, or **PHP**.

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

> [!NOTE]
> The region filename should match the `id` property. For example, a region with `"id": "header"` should be saved as `header.json`, `header.yaml`, or `header.visual.php`.

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

- **IDs**: Use lowercase with hyphens: `header`, `main-footer`
- **Names**: Use descriptive titles: `"Site Header"`, `"Main Footer"`

## Related Concepts

- **[Templates](./templates/overview.md)** - Page-specific structures (homepage, product page, etc.)
- **[Sections](./sections.md)** - Individual content blocks used in regions and templates
- **[PHP Templates](./templates/php-templates.md)** - Programmatic way to define regions with IDE support

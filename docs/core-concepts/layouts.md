# Layouts

Layouts are the **foundation** of every page in a Bagisto Visual theme.

They define the **global structure** shared across templates, including key elements like the **header**, **footer**, **meta tags**, and **scripts**.
Layouts also establish the **dynamic content area** where page-specific templates and sections are rendered.

In Bagisto Visual, layouts are built using **Blade templates** and are located under:

```plaintext
/theme/
├── resources/
│   ├── views/
│       ├── layouts/
│           ├── default.blade.php
│           ...
```

Every theme must include at least one layout file, typically named:

```
default.blade.php
```

## Core Responsibilities of a Layout

- **Render the page structure** (HTML skeleton, head, body).
- **Include regions** for shared areas (header, footer) using `@visualRegion()`.
- **Define placeholders** for dynamic template content.
- **Load styles and scripts** correctly.

## Minimal Required Layout

At minimum, every layout must include:

- `@stack('styles')` inside `<head>` — to inject page-specific styles.
- `@visual_layout_content` inside `<body>` — where template and section content is rendered.
- `@stack('scripts')` at the bottom of `<body>` — to inject page-specific scripts.

Here's a minimal working example:

```blade
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  @stack('styles')
</head>
<body>

  <main>
    {{-- Header region (shared across all pages) --}}
    @visualRegion('header')

    {{-- Page-specific template content --}}
    @visual_layout_content

    {{-- Footer region (shared across all pages) --}}
    @visualRegion('footer')
  </main>

  @stack('scripts')
</body>
</html>
```

### `@stack('styles')`

The `@stack('styles')` directive is used to inject **page-specific CSS styles** into the `<head>` section of your layout.

- Templates and sections can push additional styles to this stack using `@push('styles')`.
- This allows different pages to include their own CSS without modifying the layout manually.

### `@stack('scripts')`

The `@stack('scripts')` directive is used to inject page-specific JavaScript at the end of the `<body>` tag.

- Templates and sections can push additional JavaScript to this stack using `@push('scripts')`.
- This ensures scripts are loaded after the page content, improving performance and avoiding blocking issues.

### `@visual_layout_content`

The `@visual_layout_content` directive is a Bagisto Visual special directive that renders the content of the selected template.

- It dynamically outputs the page's sections based on the currently active template (e.g., homepage, product page).
- Without @visual_layout_content, the storefront will not display the page-specific content.

Important: Every layout must include @visual_layout_content inside the `<main>` (or equivalent) block.

### `@visualRegion()`

The `@visualRegion()` directive renders a region template (e.g., header, footer) that is shared across all pages.

- Regions are customizable by merchants through the Visual Editor.
- Common regions include `header` and `footer`.
- Example: `@visualRegion('header')` renders the header region.

See [Regions](./regions.md) for detailed documentation on creating and using regions.

## Special Layout for Customer Accounts

Bagisto Visual optionally supports a special layout for customer account pages, named:

```
account.blade.php
```

This layout can be used to create a **cleaner, simplified structure** for customer-specific areas such as login, registration, dashboard, and order management.

> **Note:**
> Using `account.blade.php` is **optional**. If not provided, customer account pages will automatically fallback to the `default.blade.php` layout.

## Developer Flexibility

Developers are **free to create and use additional layouts** based on the needs of their project.
You can define layouts for specific purposes like checkout, landing pages, or other specialized flows — there are no restrictions.

Simply create the desired layout inside `/resources/views/layouts/` and extend it in your templates:

```blade
@extends('layouts.custom-layout')
```

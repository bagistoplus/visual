# Adding Layouts

In Bagisto Visual, layouts define the **main HTML structure** of your pages.

Layouts are Blade files that act as **wrappers** for all templates and sections.
They typically include shared elements like the header, footer, meta tags, and asset loading.

## Creating the `default` Layout

The most important layout file is **`default.blade.php`**.

Create the file inside:

```text
resources/views/layouts/default.blade.php
```

Example content for a basic `default.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Awesome Theme</title>

    @bagistoVite(['resources/assets/css/app.css', 'resources/assets/js/app.js'])

    @stack('meta')
    @stack('styles')
</head>
<body>

    @visualRegion('header')

    <main>
        @visual_layout_content
    </main>

    @visualRegion('footer')

    @stack('scripts')

</body>
</html>
```

- `@visualRegion('header')` renders the header region (customizable by merchants).
- `@visualRegion('footer')` renders the footer region (customizable by merchants).
- `@visual_layout_content` renders the page content (templates and sections).
- `@bagistoVite([...])` includes theme assets correctly.

## Header and Footer Regions

Regions are customizable zones that merchants can control through the Visual Editor. Create header and footer regions:

```text
resources/views/regions/header.visual.php
resources/views/regions/footer.visual.php
```

Basic `header.visual.php` example:

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;

return TemplateBuilder::make()
    ->id('header')
    ->name('Header')
    ->sections([
        // Merchants can add sections here through the Visual Editor
    ]);
```

Basic `footer.visual.php` example:

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;

return TemplateBuilder::make()
    ->id('footer')
    ->name('Footer')
    ->sections([
        // Merchants can add sections here through the Visual Editor
    ]);
```

✅ Regions allow merchants to customize header and footer areas without touching code. Learn more about [Regions](../core-concepts/regions.md).

## Views Namespace

All views inside your theme are automatically registered under two namespaces:

- `shop::` (default and recommended)
- `awesome-theme::` (theme code namespace)

For example:

- `resources/views/layouts/default.blade.php` → `shop::layouts.default`
- `resources/views/sections/hero.blade.php` → `shop::sections.hero`
- `resources/views/components/button.blade.php` → `<x-shop::button />`

You could also use the `awesome-theme::` namespace (example: `shop::layouts.default` becomes `awesome-theme::layouts.default`),
but to keep it **simple and standard**, we always use the **`shop::` namespace** in this documentation.

## Checking Your Layout

After setting up your layout and regions:

1. Make sure you have created:

   - `resources/views/layouts/default.blade.php`
   - `resources/views/regions/header.visual.php`
   - `resources/views/regions/footer.visual.php`

2. Go to your store **homepage**.

You should now see the default layout rendered —
showing your **header region**, **main area**, and **footer region**.

<!-- ![Default Layout Render](./screenshots/default-layout-render.png) -->

✅
If you see this, it means your layout setup is working correctly!

Merchants can now customize the header and footer regions through the Visual Editor by adding sections.

# Next Steps

Now that your layout is ready, you can move on to:

- [Creating Templates](./adding-templates.md)
- [Creating Sections](./adding-sections/overview.md)

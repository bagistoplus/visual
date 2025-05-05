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

    <x-shop::header />

    <main>
        @visual_layout_content
    </main>

    <x-shop::footer />

    @stack('scripts')

</body>
</html>
```

- `<x-shop::header />` loads the header component.
- `<x-shop::footer />` loads the footer component.
- `@visual_layout_content` renders the page content (templates and sections).
- `@bagisto_vite([...], 'awesome-theme')` includes theme assets correctly.

## Header and Footer Components

Create reusable header and footer components:

```text
resources/views/components/header.blade.php
resources/views/components/footer.blade.php
```

Basic `header.blade.php` example:

```php
<header>
    <nav>
        <a href="/">Home</a>
        <a href="/products">Products</a>
        <a href="/contact">Contact</a>
    </nav>
</header>
```

Basic `footer.blade.php` example:

```php
<footer>
    <p>&copy; {{ date('Y') }} Your Company Name</p>
</footer>
```

✅ Both components are automatically registered under the `shop::` namespace.

## Views Namespace

All views inside your theme are automatically registered under two namespaces:

- `shop::` (default and recommended)
- `awesome-theme::` (theme code namespace)

For example:

- `resources/views/layouts/default.blade.php` → `shop::layouts.default`
- `resources/views/components/header.blade.php` → `<x-shop::header />`
- `resources/views/pages/home.blade.php` → `shop::pages.home`

Components must be included using **Blade component syntax**:

```php
<x-shop::header />
<x-shop::footer />
```

You could also use the `awesome-theme::` namespace (example: `<x-awesome-theme::header />`),
but to keep it **simple and standard**, we always use the **`shop::` namespace** in this documentation.

## Checking Your Layout

After setting up your layout and components:

1. Make sure you have created:

   - `resources/views/layouts/default.blade.php`
   - `resources/views/components/header.blade.php`
   - `resources/views/components/footer.blade.php`

2. Go to your store **homepage**.

You should now see the default layout rendered —
showing your basic **header**, **main area**, and **footer**.

<!-- ![Default Layout Render](./screenshots/default-layout-render.png) -->

✅
If you see this, it means your layout setup is working correctly!

# Next Steps

Now that your layout is ready, you can move on to:

- [Creating Templates](./adding-templates.md)
- [Creating Sections](./adding-sections/overview.md)

# Creating a Section

A section is a configurable UI component used to compose templates in Bagisto Visual.
Basically a section is just a Blade or Livewire component that defines structure, behavior, settings, and rendering logic.

## Generate a Section

To generate a new section, use the `visual:make-section` Artisan command:

```bash
php artisan visual:make-section AnnouncementBar --theme=awesome-theme
```

This will create a basic Blade section class named AnnouncementBar inside the awesome-theme package:

```text
packages/Themes/AwesomeTheme/src/Sections/AnnouncementBar.php
```

Generated class content:

```php
<?php

namespace YourVendor\AwesomeTheme\Sections;

use BagistoPlus\Visual\Sections\BladeSection;

class AnnouncementBar extends BladeSection
{
    protected static string $view = 'shop::sections.announcement-bar';

    public static function settings(): array
    {
        return [];
    }

    public static function blocks(): array
    {
        return [];
    }
}
```

It also generates the corresponding Blade view file:

```text
packages/Themes/AwesomeTheme/resources/views/sections/announcement-bar.blade.php
```

Default Blade content:

```blade
<div>
  Announcement Bar
</div>
```

::: info
Ensure the theme is already installed:

```bash
composer require themes/awesome-theme
```

:::

### `--livewire`

Use this flag to generate a Livewire-based section:

```bash
php artisan visual:make-section AnnouncementBar --livewire --theme=awesome-theme
```

This creates a class that extends LivewireSection and includes a Livewire component stub.

### No `--theme`

If omitted, the section is placed in:

```text
app/Visual/Sections/AnnouncementBar.php
```

## Registering Sections

Bagisto Visual automatically discovers sections from:

- `app/Visual/Sections`
- `packages/<Vendor>/<Theme>/src/Sections`

For other locations, you can manually register sections in a service provider:

### Discover a directory

```php
Visual::discoverSectionsIn(base_path('modules/Shared/Sections'));
```

### Register a single class

```php
Visual::registerSection(\App\Custom\Sections\PromoBanner::class);
```

Theme packages: use a vendor prefix

```php
Visual::registerSection(
    \Themes\AwesomeTheme\Sections\AnnouncementBar::class,
    'awesome-theme'
);
```

This ensures that multiple themes can define sections with the same slug without conflict.

---

Next: [Section Attributes](./section-attributes.md)

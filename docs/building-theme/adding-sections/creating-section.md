# Creating a Section

A section is a configurable UI component used to compose templates in Bagisto Visual.
Basically a section is just a Blade or Livewire component that defines structure, behavior, settings, and rendering logic.

## Generate a Section

To generate a new section, use the `visual:make-section` Artisan command:

```bash
php artisan visual:make-section AnnouncementBar --theme=awesome-theme
```

This will create a basic section class named `AnnouncementBar` inside the awesome-theme package:

```text
packages/Themes/AwesomeTheme/src/Sections/AnnouncementBar.php
packages/Themes/AwesomeTheme/resources/views/sections/announcement-bar.blade.php
```

### Interactive Mode

You can omit arguments to use interactive prompts:

```bash
php artisan visual:make-section
```

The command will prompt you for:
- **Section name** (e.g., `AnnouncementBar`)
- **Target theme** (selects from installed Visual themes or `app/Visual`)

## Command Options

### Section Types

The command generates different section types based on flags:

| Option | Section Type | Description |
|--------|--------------|-------------|
| *(none)* | `SimpleSection` | **Default.** Lightweight section. Best for simple sections that don't need component features. |
| `--component` | `BladeSection` | Blade component-based section. Use when you prefer Blade component patterns. |
| `--livewire` | `LivewireSection` | Livewire component-based section. Use when you need reactive behavior or real-time updates. |

::: info
All section types support child blocks. The choice between `SimpleSection`, `BladeSection`, and `LivewireSection` is based on your preferred development style and feature needs.
:::

::: warning
You cannot use both `--component` and `--livewire` flags together.
:::

### Other Options

| Option | Description |
|--------|-------------|
| `--theme=awesome-theme` | Target theme slug. Omit to use interactive selection. |
| `--force` | Overwrite existing section files if they already exist. |

## Generated Files

### Default Section (SimpleSection)

**Command:**
```bash
php artisan visual:make-section AnnouncementBar --theme=awesome-theme
```

**Generated class:**
```php
<?php

namespace YourVendor\AwesomeTheme\Sections;

use BagistoPlus\Visual\Blocks\SimpleSection;

class AnnouncementBar extends SimpleSection
{
    protected static string $view = 'shop::sections.announcement-bar';

    public static function settings(): array
    {
        // section settings
        return [];
    }
}
```

**Generated view** (`resources/views/sections/announcement-bar.blade.php`):
```blade
<div>
    <!-- AnnouncementBar -->
</div>
```

### Blade Component Section (BladeSection)

**Command:**
```bash
php artisan visual:make-section Header --component --theme=awesome-theme
```

**Generated class:**
```php
<?php

namespace YourVendor\AwesomeTheme\Sections;

use BagistoPlus\Visual\Blocks\BladeSection;

class Header extends BladeSection
{
    protected static string $view = 'shop::sections.header';

    public static function settings(): array
    {
        // section settings
        return [];
    }
}
```

### Livewire Section (LivewireSection)

**Command:**
```bash
php artisan visual:make-section ProductFilters --livewire --theme=awesome-theme
```

**Generated class:**
```php
<?php

namespace YourVendor\AwesomeTheme\Sections;

use BagistoPlus\Visual\Blocks\LivewireSection;

class ProductFilters extends LivewireSection
{
    protected static string $view = 'shop::sections.product-filters';

    public static function settings(): array
    {
        // section settings
        return [];
    }
}
```

## Generate in `app/Visual`

If you omit the `--theme` option, the command shows an interactive menu including an **"In default app"** option:

```bash
php artisan visual:make-section AnnouncementBar
```

```
🎨 Select the target theme:
  awesome-theme
  another-theme
> In default app
```

This generates files in your application directory:

```text
app/Visual/Sections/AnnouncementBar.php
resources/views/sections/announcement-bar.blade.php
```

**Namespace:** `App\Visual\Sections`

::: info
Sections in `app/Visual` are useful for:
- Quick prototyping
- Application-specific sections not tied to a theme
- Shared sections used across multiple themes
:::

## Overwriting Existing Files

Use the `--force` flag to overwrite existing section files:

```bash
php artisan visual:make-section AnnouncementBar --theme=awesome-theme --force
```

Without `--force`, the command will error if files already exist:

```
❌ Section class already exists: packages/.../AnnouncementBar.php (use --force to overwrite)
```

## Registering Sections

Bagisto Visual automatically discovers sections from:

- `app/Visual/Sections`
- `packages/<Vendor>/<Theme>/src/Sections`

For other locations, you can manually register sections in a service provider:

### Discover a directory

Use `discoverSectionsIn()` to auto-discover all sections in a directory. The method requires two parameters:
- The directory path containing your section classes
- The base namespace for those sections (defaults to `'App\\Sections'`)

```php
Visual::discoverSectionsIn(
    base_path('modules/Shared/Sections'),
    'Modules\\Shared\\Sections'
);
```

This will automatically discover and register all section classes in the specified directory, matching the namespace structure to the folder structure.

### Register a single class

```php
Visual::registerSection(\App\Custom\Sections\PromoBanner::class);
```

Or for theme packages:

```php
Visual::registerSection(\Themes\AwesomeTheme\Sections\AnnouncementBar::class);
```

## Examples

### Create a simple section in a theme
```bash
php artisan visual:make-section Hero --theme=awesome-theme
```

### Create a Blade component section
```bash
php artisan visual:make-section Footer --component --theme=awesome-theme
```

### Create a Livewire section for interactive features
```bash
php artisan visual:make-section SearchFilters --livewire --theme=awesome-theme
```

### Create in app/Visual with interactive prompts
```bash
php artisan visual:make-section
# Select "In default app" from the menu
```

### Force overwrite existing section
```bash
php artisan visual:make-section Hero --theme=awesome-theme --force
```

---

Next: [Section Attributes](./section-attributes.md)
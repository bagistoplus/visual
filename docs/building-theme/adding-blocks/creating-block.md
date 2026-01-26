# Creating a Block

A block is a reusable UI component that can be added to sections in Bagisto Visual.
This guide walks you through creating a custom block step-by-step. We'll build a **Feature** block that displays an icon, heading, and description.

## Generate a Block

To generate a new block, use the `visual:make-block` Artisan command:

```bash
php artisan visual:make-block Feature --theme=awesome-theme
```

This will create a basic block class named `Feature` inside the awesome-theme package:

```text
packages/Themes/AwesomeTheme/src/Blocks/Feature.php
packages/Themes/AwesomeTheme/resources/views/blocks/feature.blade.php
```

### Interactive Mode

You can omit arguments to use interactive prompts:

```bash
php artisan visual:make-block
```

The command will prompt you for:
- **Block name** (e.g., `Feature`)
- **Target theme** (selects from installed Visual themes or `app/Visual`)

## Command Options

### Block Types

The command generates different block types based on flags:

| Option | Block Type | Description |
|--------|------------|-------------|
| *(none)* | `SimpleBlock` | **Default.** Lightweight block. Best for simple blocks that don't need component features. |
| `--component` | `BladeBlock` | Blade component-based block. Use when you prefer Blade component patterns. |
| `--livewire` | `LivewireBlock` | Livewire component-based block. Use when you need reactive behavior or real-time updates. |

::: info
The choice between `SimpleBlock`, `BladeBlock`, and `LivewireBlock` is based on your preferred development style and feature needs.
:::

::: warning
You cannot use both `--component` and `--livewire` flags together.
:::

### Other Options

| Option | Description |
|--------|-------------|
| `--theme=awesome-theme` | Target theme slug. Omit to use interactive selection. |
| `--force` | Overwrite existing block files if they already exist. |

## Generated Files

### Default Block (SimpleBlock)

**Command:**
```bash
php artisan visual:make-block Feature --theme=awesome-theme
```

**Generated class:**
```php
<?php

namespace YourVendor\AwesomeTheme\Blocks;

use BagistoPlus\Visual\Block\SimpleBlock;

class Feature extends SimpleBlock
{
    protected static string $view = 'shop::blocks.feature';

    public static function settings(): array
    {
        // block settings
        return [];
    }
}
```

**Generated view** (`resources/views/blocks/feature.blade.php`):
```blade
<div>
    <!-- Feature -->
</div>
```

### Blade Component Block (BladeBlock)

**Command:**
```bash
php artisan visual:make-block Feature --component --theme=awesome-theme
```

**Generated class:**
```php
<?php

namespace YourVendor\AwesomeTheme\Blocks;

use BagistoPlus\Visual\Block\BladeBlock;

class Feature extends BladeBlock
{
    protected static string $view = 'shop::blocks.feature';

    public static function settings(): array
    {
        // block settings
        return [];
    }
}
```

### Livewire Block (LivewireBlock)

**Command:**
```bash
php artisan visual:make-block AddToCart --livewire --theme=awesome-theme
```

**Generated class:**
```php
<?php

namespace YourVendor\AwesomeTheme\Blocks;

use BagistoPlus\Visual\Block\LivewireBlock;

class AddToCart extends LivewireBlock
{
    protected static string $view = 'shop::blocks.add-to-cart';

    public static function settings(): array
    {
        // block settings
        return [];
    }
}
```

## Generate in `app/Visual`

If you omit the `--theme` option, the command shows an interactive menu including an **"In default app"** option:

```bash
php artisan visual:make-block Feature
```

```
🧱 Select the target theme:
  awesome-theme
  another-theme
> In default app
```

This generates files in your application directory:

```text
app/Visual/Blocks/Feature.php
resources/views/blocks/feature.blade.php
```

**Namespace:** `App\Visual\Blocks`

::: info
Blocks in `app/Visual` are useful for:
- Quick prototyping
- Application-specific blocks not tied to a theme
- Shared blocks used across multiple themes
:::

## Overwriting Existing Files

Use the `--force` flag to overwrite existing block files:

```bash
php artisan visual:make-block Feature --theme=awesome-theme --force
```

Without `--force`, the command will error if files already exist:

```
❌ Block class already exists: packages/.../Feature.php (use --force to overwrite)
```

## Registering Blocks

Bagisto Visual automatically discovers blocks from:

- `app/Visual/Blocks`
- `packages/<Vendor>/<Theme>/src/Blocks`

For other locations, you can manually register blocks in a service provider:

### Discover a directory

Use `discoverBlocksIn()` to auto-discover all blocks in a directory. The method requires two parameters:
- The directory path containing your block classes
- The base namespace for those blocks (defaults to `'App\\Blocks'`)

```php
Visual::discoverBlocksIn(
    base_path('modules/Shared/Blocks'),
    'Modules\\Shared\\Blocks'
);
```

This will automatically discover and register all block classes in the specified directory, matching the namespace structure to the folder structure.

### Register a single class

```php
Visual::registerBlock(\App\Custom\Blocks\Feature::class);
```

Or for theme packages:

```php
Visual::registerBlock(\Themes\AwesomeTheme\Blocks\Feature::class);
```

## Complete Example

Let's build a complete **Feature** block with settings and a view:

### Step 1: Add Settings to the Block Class

Edit the generated `Feature.php` class to add settings:

```php
<?php

namespace Themes\AwesomeTheme\Blocks;

use BagistoPlus\Visual\Block\BladeBlock;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Textarea;
use BagistoPlus\Visual\Settings\Icon;
use BagistoPlus\Visual\Settings\Color;

class Feature extends BladeBlock
{
    protected static string $view = 'shop::blocks.feature';

    public static function settings(): array
    {
        return [
            Icon::make('icon', 'Feature icon')
                ->default('heroicon-o-star'),

            Text::make('heading', 'Heading')
                ->default('Amazing Feature'),

            Textarea::make('description', 'Description')
                ->default('This feature will transform your business.'),

            Color::make('icon_color', 'Icon color')
                ->default('#4f46e5'),
        ];
    }
}
```

### Step 2: Create the Blade View

Update the generated view in `resources/views/blocks/feature.blade.php`:

```blade
<div {{ $block->editor_attributes }} class="feature-block">
    <div class="feature-block__icon" style="color: {{ $block->settings->icon_color }}">
        <x-icon name="{{ $block->settings->icon }}" class="w-12 h-12" />
    </div>

    <h3 class="feature-block__heading">
        {{ $block->settings->heading }}
    </h3>

    <p class="feature-block__description">
        {{ $block->settings->description }}
    </p>
</div>
```

### The $block Object

The `$block` variable is automatically injected into every block view:

- `$block->settings` - Access block settings
- `$block->id` - Unique block identifier
- `$block->type` - Block type name
- `$block->editor_attributes` - Required attributes for Visual Editor integration
- `$block->index` - Block's position index (useful for conditional rendering)

---

Next: [Block Schema](./block-schema.md)

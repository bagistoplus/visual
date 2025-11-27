# Creating a Block

This guide walks you through creating a custom block step-by-step. We'll build a **Feature** block that displays an icon, heading, and description.

## Step 1: Create the PHP Block Class

Create a new PHP file in `src/Blocks/Feature.php`:

```php
<?php

namespace Themes\YourTheme\Blocks;

use BagistoPlus\Visual\Block\BladeBlock;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Textarea;
use BagistoPlus\Visual\Settings\Icon;
use BagistoPlus\Visual\Settings\Color;

class Feature extends BladeBlock
{
    /**
     * The Blade view to render this block
     */
    protected static string $view = 'shop::blocks.feature';

    /**
     * Define the block's configurable settings
     */
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

### Key Components

- **Namespace**: Must match your theme's namespace structure
- **Extends BladeBlock**: The most common base class for blocks
- **$view property**: Points to the Blade template (using dot notation)
- **settings() method**: Defines the settings merchants can configure

## Step 2: Create the Blade View

Create the corresponding Blade view in `resources/views/blocks/feature.blade.php`:

```blade
<div class="feature-block">
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
- `$block->blocks` - Child blocks (for container blocks)

## Step 3: Register the Block (Optional)

Blocks are auto-discovered from the `src/Blocks/` directory. No manual registration needed!

However, if you want to control the block's display name in the theme editor, add a `name()` method:

```php
public static function name(): string
{
    return 'Feature';
}
```

## Step 4: Use the Block in a Section

Now your block is ready to be used! Add it to a section's schema:

```php
// In your section class
public static function schema(): array
{
    return [
        'blocks' => [
            'feature',  // Block type name (class name in kebab-case)
        ],
    ];
}
```

## Complete Example: Product Badge Block

Here's a more complete example showing a Product Badge block with presets:

**PHP Class** (`src/Blocks/ProductBadge.php`):
```php
<?php

namespace Themes\YourTheme\Blocks;

use BagistoPlus\Visual\Block\BladeBlock;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Select;
use BagistoPlus\Visual\Settings\Color;

class ProductBadge extends BladeBlock
{
    protected static string $view = 'shop::blocks.product-badge';

    public static function name(): string
    {
        return 'Product Badge';
    }

    public static function settings(): array
    {
        return [
            Text::make('text', 'Badge text')
                ->default('New'),

            Select::make('style', 'Badge style')
                ->options([
                    'default' => 'Default',
                    'success' => 'Success',
                    'warning' => 'Warning',
                    'danger' => 'Danger',
                ])
                ->default('default'),

            Color::make('background_color', 'Background color'),
            Color::make('text_color', 'Text color'),
        ];
    }

    public static function presets(): array
    {
        return [
            [
                'name' => 'New',
                'settings' => [
                    'text' => 'New',
                    'style' => 'success',
                ],
            ],
            [
                'name' => 'Sale',
                'settings' => [
                    'text' => 'Sale',
                    'style' => 'danger',
                ],
            ],
            [
                'name' => 'Limited',
                'settings' => [
                    'text' => 'Limited Edition',
                    'style' => 'warning',
                ],
            ],
        ];
    }
}
```

**Blade View** (`resources/views/blocks/product-badge.blade.php`):
```blade
<span class="product-badge product-badge--{{ $block->settings->style }}"
      @if($block->settings->background_color || $block->settings->text_color)
      style="
        @if($block->settings->background_color)
            background-color: {{ $block->settings->background_color }};
        @endif
        @if($block->settings->text_color)
            color: {{ $block->settings->text_color }};
        @endif
      "
      @endif>
    {{ $block->settings->text }}
</span>
```

## Block Base Types

Choose the right base class for your block:

### BladeBlock

Use for most blocks with settings and Blade views.

```php
class MyBlock extends BladeBlock
{
    protected static string $view = 'shop::blocks.my-block';

    public static function settings(): array
    {
        return [/* settings */];
    }
}
```

### LivewireBlock

Use for interactive blocks requiring JavaScript/AJAX functionality.

```php
class InteractiveBlock extends LivewireBlock
{
    protected static string $component = 'shop.blocks.interactive';

    public $value = '';

    public function updated($property)
    {
        // Livewire lifecycle methods
    }
}
```

### SimpleBlock

Use for structural blocks without settings (dividers, spacers).

```php
class Divider extends SimpleBlock
{
    protected static string $view = 'shop::blocks.divider';
}
```

## Best Practices

✅ **One responsibility**: Each block should do one thing well
✅ **Descriptive names**: Use clear, meaningful class and setting names
✅ **Sensible defaults**: Provide good default values for all settings
✅ **Validation**: Use setting validation for required fields
✅ **Documentation**: Comment complex logic for future maintainers
✅ **Responsive design**: Test blocks on mobile, tablet, and desktop
✅ **Accessibility**: Include proper ARIA labels and semantic HTML

## Next Steps

- **[Block Schema](/building-theme/adding-blocks/block-schema)**: Learn about settings, presets, and advanced schema options
- **[Static vs Dynamic Blocks](/building-theme/adding-blocks/static-vs-dynamic-blocks)**: Understand when to use each type
- **[Container Blocks](/building-theme/adding-blocks/container-blocks)**: Create blocks that accept child blocks

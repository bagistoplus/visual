# Container Blocks (Nesting)

Container blocks are blocks that accept child blocks, enabling deep nesting and sophisticated page layouts. This feature transforms the theme editor into a true page builder.

## What are Container Blocks?

Container blocks are regular blocks with one key difference: they can accept other blocks as children. This enables:

- **Multi-column layouts** with different content in each column
- **Tabbed content** with blocks inside each tab
- **Accordions** with rich content in each panel
- **Nested structures** up to 8 levels deep

## Creating a Container Block

To create a container block, implement the `blocks()` method:

```php
<?php

namespace Themes\YourTheme\Blocks;

use BagistoPlus\Visual\Block\BladeBlock;
use BagistoPlus\Visual\Settings\Select;

class Columns extends BladeBlock
{
    protected static string $view = 'shop::blocks.columns';

    public static function settings(): array
    {
        return [
            Select::make('column_count', 'Number of Columns')
                ->options([
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                ])
                ->default('3'),
        ];
    }

    /**
     * Define which blocks can be nested
     */
    public static function blocks(): array
    {
        return ['@theme'];  // Accept all theme blocks
    }
}
```

## Rendering Child Blocks

In the container block's view, use the `@children` directive to render child blocks:

```blade
{{-- resources/views/blocks/columns.blade.php --}}
<div class="columns columns--{{ $block->settings->column_count }}">
    @children
</div>
```

> **Note**: The `@children` directive automatically renders all child blocks. For advanced layouts requiring custom wrappers per child block, consult the Visual package documentation for available rendering patterns.

## Container Block Examples

### Example 1: Tabs Block

```php
class Tabs extends BladeBlock
{
    protected static string $view = 'shop::blocks.tabs';

    public static function settings(): array
    {
        return [
            Select::make('style', 'Tab Style')
                ->options([
                    'default' => 'Default',
                    'pills' => 'Pills',
                    'underline' => 'Underline',
                ])
                ->default('default'),
        ];
    }

    public static function blocks(): array
    {
        return ['@theme'];
    }
}
```

```blade
{{-- View: resources/views/blocks/tabs.blade.php --}}
<div class="tabs tabs--{{ $block->settings->style }}">
    {{-- Simple rendering --}}
    @children
</div>
```

> **Note**: For complex tab navigation with headers, consult the Visual package documentation for advanced rendering patterns.

### Example 2: Accordion Block

```php
class Accordion extends BladeBlock
{
    protected static string $view = 'shop::blocks.accordion';

    public static function settings(): array
    {
        return [
            Checkbox::make('allow_multiple', 'Allow Multiple Open')
                ->default(false),
        ];
    }

    public static function blocks(): array
    {
        return ['accordion-item'];  // Only accept specific block type
    }
}
```

```blade
{{-- View: resources/views/blocks/accordion.blade.php --}}
<div class="accordion"
     data-allow-multiple="{{ $block->settings->allow_multiple ? 'true' : 'false' }}">
    @children
</div>
```

### Example 3: Generic Container

```php
class Container extends BladeBlock
{
    protected static string $view = 'shop::blocks.container';

    public static function settings(): array
    {
        return [
            Select::make('padding', 'Padding')
                ->options([
                    'none' => 'None',
                    'small' => 'Small',
                    'medium' => 'Medium',
                    'large' => 'Large',
                ])
                ->default('medium'),

            Color::make('background_color', 'Background Color'),
        ];
    }

    public static function blocks(): array
    {
        return ['@theme'];
    }
}
```

```blade
{{-- View: resources/views/blocks/container.blade.php --}}
<div class="container container--padding-{{ $block->settings->padding }}"
     @if($block->settings->background_color)
     style="background-color: {{ $block->settings->background_color }}"
     @endif>
    @children
</div>
```

## Controlling Accepted Block Types

### Accept All Theme Blocks

```php
public static function blocks(): array
{
    return ['@theme'];
}
```

### Accept Specific Block Types

```php
public static function blocks(): array
{
    return [
        'button',
        'image',
        'heading',
        'paragraph',
    ];
}
```

### Mix Both Approaches

```php
public static function blocks(): array
{
    return [
        '@theme',          // All theme blocks
        'custom-block',    // Plus custom blocks
    ];
}
```


## Deep Nesting

Blocks can be nested up to **8 levels deep**, creating complex hierarchies:

```
Section
└── Columns Block (Level 1)
    ├── Column 1
    │   └── Tabs Block (Level 2)
    │       ├── Tab 1
    │       │   └── Accordion Block (Level 3)
    │       │       └── Accordion Items (Level 4)
    │       └── Tab 2
    │           └── Image Block (Level 3)
    └── Column 2
        └── Container Block (Level 2)
            ├── Heading Block (Level 3)
            └── Button Block (Level 3)
```

### Example: Multi-Level Nesting

A merchant could build:

1. **Columns** block with 2 columns
2. Left column contains a **Tabs** block
3. Each tab contains an **Accordion** block
4. Inside accordions: images, text, buttons

This creates a sophisticated, fully customizable layout without code.

## Advanced Patterns

The `@children` directive handles most rendering scenarios. For advanced patterns like conditional rendering, grouping, or custom wrappers, consult the Visual package documentation.

## Real-World Use Case: Product Card Builder

Allow merchants to build custom product cards using granular product blocks:

```php
class ProductCard extends BladeBlock
{
    protected static string $view = 'shop::blocks.product-card';

    public static function blocks(): array
    {
        return [
            'product-image',
            'product-title',
            'product-price',
            'product-rating',
            'product-badge',
            'button',
        ];
    }
}
```

```blade
<div class="product-card">
    @children
</div>
```

Merchants can now create completely custom product cards:
- Fashion store: Large image → Badge → Title → Price → Quick add
- Electronics: Title → Image gallery → Price → Rating → Add to cart
- Handmade: Image → Badge → Title → Artist name → Price

## Best Practices

✅ **Provide sensible defaults**: Container should work empty or with children
✅ **Set max blocks**: Prevent overly complex nesting that hurts performance
✅ **Use semantic structure**: Container HTML should make sense
✅ **Handle empty state**: Show placeholder or message when no children
✅ **Test nesting**: Verify blocks render correctly when nested
✅ **Document restrictions**: Make clear which blocks are accepted
✅ **Style children appropriately**: Ensure child blocks fit the layout

## Debugging Container Blocks

```blade
{{-- See child block count --}}
<div data-child-count="{{ $block->blocks->count() }}">
    @children
</div>

{{-- Dump block data --}}
@dump($block->blocks)
```

## Next Steps

- **[Using in Sections](/building-theme/adding-blocks/using-in-sections)**: How sections accept and render container blocks
- **[Rendering Blocks](/building-theme/adding-blocks/rendering-blocks)**: Master block rendering techniques
- **[Block Schema](/building-theme/adding-blocks/block-schema)**: Configure accepted blocks and limits

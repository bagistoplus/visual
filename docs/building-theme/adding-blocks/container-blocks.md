# Container Blocks (Nesting)

Container blocks are blocks that accept child blocks, enabling deep nesting and sophisticated page layouts. This feature transforms the theme editor into a true page builder.

## What are Container Blocks?

Container blocks are regular blocks with one key difference: they can accept other blocks as children. This enables:

- **Multi-column layouts** with different content in each column
- **Tabbed content** with blocks inside each tab
- **Accordions** with rich content in each panel
- **Nested structures** up to 8 levels deep

## Creating a Container Block

To create a container block, define the `$accepts` property:

```php
<?php

namespace Themes\YourTheme\Blocks;

use BagistoPlus\Visual\Block\SimpleBlock;
use BagistoPlus\Visual\Settings\Select;

class Columns extends SimpleBlock
{
    protected static string $view = 'shop::blocks.columns';

    /**
     * Define which blocks can be nested
     */
    protected static array $accepts = ['@awesome-theme/*'];  // Accept all theme blocks

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

## Sharing Data with Child Blocks

Container blocks can share data with their children using the `share()` method. Data returned from `share()` is automatically passed down the entire nested structure, making it available to all descendant blocks.

### How It Works

The `share()` method returns an array of data that becomes available in:

1. **Child block classes**: Access via `$this->context('key', 'default')`
2. **Child block views**: Available as variables (e.g., `$product`, `$category`)
3. **Child block presets**: Use dynamic sources with `@key` syntax (e.g., `'@product.name'`)
4. **Deeply nested blocks**: Automatically cascades to grandchildren and beyond

### Example 1: Product Card Sharing Product Data

The ProductCard container shares the product object so child blocks don't need their own product settings:

```php
class ProductCard extends SimpleBlock
{
    protected static string $view = 'shop::blocks.product-card';

    protected static array $accepts = [
        '@awesome-theme/product-image',
        '@awesome-theme/product-title',
        '@awesome-theme/product-price',
    ];

    public static function settings(): array
    {
        return [
            Product::make('product', 'Product'),
        ];
    }

    public function share(): array
    {
        return [
            'product' => $this->block->settings->product ?? $this->context('product')
        ];
    }
}
```

Now child blocks can access the shared product data:

**In child block classes:**
```php
class ProductImage extends SimpleBlock
{
    protected function getViewData(): array
    {
        $product = $this->context('product');

        return [
            'imageUrl' => $product?->base_image->url,
        ];
    }
}
```

**In child block views:**
```blade
{{-- In product-image.blade.php view --}}
@if($product)
    <img src="{{ $product->base_image->url }}" alt="{{ $product->name }}">
@endif
```

**In child block presets using dynamic sources:**
```php
PresetBlock::make('@awesome-theme/product-image')
    ->settings([
        'src' => '@product.base_image.url',
        'alt' => '@product.name',
    ]),
PresetBlock::make('@awesome-theme/product-title')
    ->settings([
        'text' => '@product.name',
        'url' => '@product.url',
    ]),
```

The `@` syntax automatically resolves to the shared data at runtime.

### Example 2: Accordion Sharing Icon Type

The Accordion container shares its icon setting with all accordion items:

```php
class Accordion extends SimpleBlock
{
    protected static string $view = 'shop::blocks.accordion';

    protected static array $accepts = ['@awesome-theme/accordion-item'];

    public static function settings(): array
    {
        return [
            Select::make('icon', 'Icon Type')
                ->options([
                    'caret' => 'Caret',
                    'plus' => 'Plus/Minus',
                ])
                ->default('caret'),
        ];
    }

    public function share(): array
    {
        return [
            'accordionIconType' => $this->block->settings->icon ?? 'caret',
        ];
    }
}
```

All accordion items automatically receive the icon type:

```blade
{{-- In accordion-item.blade.php view --}}
<button class="accordion-trigger">
    <span>{{ $block->settings->title }}</span>
    <span class="icon">
        @if ($accordionIconType === 'caret')
            <x-icon name="chevron-down" />
        @else
            <x-icon name="plus" />
        @endif
    </span>
</button>
```

---

## Next Steps

- **[Block Attributes](/building-theme/adding-blocks/block-schema)**: Configure accepted blocks and limits
- **[Section Attributes](/building-theme/adding-sections/section-attributes)**: Configure which blocks sections accept

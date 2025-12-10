# Static Blocks

Static blocks are blocks that are rendered directly in Blade templates rather than being added through the Visual Editor. They provide a way for developers to maintain control over section structure while still allowing merchants to customize block settings.

Unlike dynamic blocks (which merchants can add, remove, and reorder through the editor), static blocks have a fixed position in the section layout defined by the developer.

::: info Why Use Static Blocks Instead of Direct Components?
Even though static blocks have a fixed position, they remain fully editable in the Visual Editor. Merchants can click on static blocks and customize their settings (text, colors, images, etc.). Static blocks can even accept their own children that merchants can add, remove, and reorder — making the content fully dynamic and merchant-controlled while the structure remains developer-controlled.
:::

## When to Use Static Blocks

Static blocks are ideal for:

- **Structural consistency**: Elements that are core to the section's design and shouldn't be removed
- **Conditional rendering**: Blocks that appear or disappear based on section settings
- **Fixed relationships**: Elements that must always appear together (like an icon paired with a heading)
- **Layout controls**: UI elements like slideshow navigation arrows or accordion icons

## Rendering Static Blocks

To render a static block in your section's Blade view, use the `@visualBlock` directive:

```blade
@visualBlock('type', 'unique-id')
```

### Parameters

- **`type`**: The block type (e.g., `'heading'`, `'@awesome-theme/button'`)
- **`id`**: A unique identifier for this block instance within the section

### Basic Example

```blade
<div class="hero-section">
    {{-- Static heading block - merchants can edit the text --}}
    @visualBlock('heading', 'hero-title')

    {{-- Static paragraph block - merchants can edit the content --}}
    @visualBlock('paragraph', 'hero-description')

    {{-- Dynamic blocks area - merchants can add/remove/reorder blocks --}}
    @children
</div>
```

::: warning Important
The `id` parameter must be unique within the section. Use descriptive IDs that indicate the block's purpose (e.g., `'hero-title'`, `'footer-logo'`, `'slide-1-caption'`).
:::

## Passing Data to Static Blocks

You can pass custom data to static blocks using an optional third parameter. These attributes are injected into the block's view context and accessible as variables.

### Syntax

```blade
@visualBlock('type', 'id', ['attribute' => 'value'])
```

### Basic Example

```blade
@visualBlock('@awesome-theme/heading', 'hero-title', [
    'title' => 'Welcome to Our Store',
    'subtitle' => 'Shop the latest trends'
])
```

In the block's view, these attributes are accessible as variables:

```blade
<!-- resources/views/blocks/heading.blade.php -->
<div {{ $block->editor_attributes }}>
    <h1>{{ $title }}</h1>
    <p>{{ $subtitle }}</p>
</div>
```

### With Dynamic Data

Pass data from the section or other sources:

```blade
@visualBlock('@awesome-theme/product-card', 'featured-product', [
    'product' => $featuredProduct,
    'showPrice' => true,
])
```

Block view:

```blade
<div {{ $block->editor_attributes }} class="product-card">
    <h3>{{ $product->name }}</h3>

    @if($showPrice)
        <p class="price">{{ $product->price }}</p>
    @endif
</div>
```

## Conditional Rendering

One of the key advantages of static blocks is the ability to conditionally render them based on section settings:

```blade
<div class="features-section">
    {{-- Always visible --}}
    @visualBlock('heading', 'features-title')

    {{-- Conditionally visible based on setting --}}
    @if($section->settings->show_subtitle)
        @visualBlock('paragraph', 'features-subtitle')
    @endif

    {{-- Dynamic feature blocks --}}
    <div class="features-grid">
        @children
    </div>
</div>
```

The Visual Editor provides visual cues to merchants when conditional static blocks are hidden, helping them understand why a block isn't currently visible.

### Rendering in Loops

Static blocks can also be rendered in loops. Each instance will share the same structure and settings configured by the merchant in the Visual Editor:

```blade
<div class="testimonials-grid">
    @foreach ($testimonials as $testimonial)
        @visualBlock('@awesome-theme/testimonial-card', 'testimonial-item', [
            'testimonial' => $testimonial,
        ])
    @endforeach
</div>
```

In this example:

- The merchant can customize the `testimonial-item` block's settings (colors, layout, etc.) **once** in the Visual Editor
- All instances in the loop share the same configuration
- Each instance receives different data via the attributes array (`$testimonial`)

This is useful for displaying dynamic lists (products, testimonials, blog posts) where you want:

- ✅ Consistent styling across all items (merchant controls design)
- ✅ Different data for each item (developer controls data)
- ✅ Fixed structure (developer controls which items appear)

## Configuring Static Blocks in Presets

One powerful feature of static blocks is the ability to pre-configure them in section presets. When you define a static block with an ID, you can reference that ID in the preset to provide default settings and child block structure.

**Section Blade:**

```blade
<div {{ $section->editor_attributes }} class="product-grid-section">
    <div class="grid grid-cols-4 gap-4">
        @foreach ($products as $product)
            {{-- Static block repeated in loop --}}
            @visualBlock('@awesome-theme/product-card', 'static-product-card', [
                'product' => $product,
            ])
        @endforeach
    </div>
</div>
```

**Section Preset:**

```php
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

public static function presets(): array
{
    return [
        Preset::make('Product Grid')
            ->settings([
                'columns' => 4,
                'gap' => 4,
            ])
            ->blocks([
                PresetBlock::make('@awesome-theme/heading')
                    ->settings(['text' => 'Featured Products']),

                // Configure the static product card default structure
                PresetBlock::make('@awesome-theme/product-card')
                    ->id('static-product-card')       // References static block by ID
                    ->static()                         // Marks as static (required)
                    ->settings([
                        'border_radius' => 'lg',
                        'shadow' => true,
                    ])
                    ->children([                       // Define default child structure
                        PresetBlock::make('@awesome-theme/product-image')
                            ->settings([
                                'aspect_ratio' => 'square',
                                'object_fit' => 'cover',
                            ]),

                        PresetBlock::make('@awesome-theme/product-title')
                            ->settings([
                                'tag' => 'h3',
                                'size' => 'lg',
                            ]),

                        PresetBlock::make('@awesome-theme/product-price')
                            ->settings(['show_compare_price' => true]),

                        PresetBlock::make('@awesome-theme/button')
                            ->settings([
                                'text' => 'Add to Cart',
                                'style' => 'primary',
                            ]),
                    ])
            ])
    ];
}
```

When a merchant adds this section from the preset, the Visual Editor shows one instance of the static `static-product-card` block with:

- Pre-configured settings (border radius, shadow)
- All child blocks already in place (image, title, price, button)
- Each child block with its own default settings

The merchant customizes this one instance in the Visual Editor, and the configuration applies to all cards in the loop on the storefront.

Merchants can:

- ✅ Edit settings of the static block and its children (applies to all instances)
- ✅ Add, remove, or reorder the child blocks inside (applies to all instances)
- ❌ Cannot move or remove the block itself (it's static and controlled by the loop)

This provides a complete, ready-to-use structure with consistent styling across all loop instances.

---

Next: [Container Blocks](./container-blocks.md)

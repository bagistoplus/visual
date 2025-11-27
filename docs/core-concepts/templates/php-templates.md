# PHP Templates

PHP templates (`.visual.php`) provide a **programmatic, type-safe alternative** to JSON and YAML templates. They use the `TemplateBuilder` fluent API to define page structure while maintaining full compatibility with the Theme Editor.

::: tip Alternative to JSON/YAML
PHP templates are a **first-class alternative** to JSON/YAML templates, not a replacement. Choose the format that best fits your workflow:

- **JSON/YAML**: Simple, declarative, designer-friendly → [Learn more](./json-yaml.md)
- **PHP**: Programmatic, IDE support, developer-friendly
  :::

## Why Use PHP Templates?

PHP templates offer developer-focused benefits while producing the same merchant-editable result as JSON/YAML:

✅ **IDE Autocomplete** - Full IntelliSense support

✅ **Type Safety** - Catch errors at development time

✅ **PHP Features** - Use variables, loops, conditionals

✅ **Preset Classes** - Import and use preset classes directly

✅ **Refactoring** - Easy to rename, move, and organize

✅ **Comments** - Document complex logic inline

✅ **Theme Editor Compatible** - Merchants can still customize everything visually

## Quick Comparison

Here's the same template in JSON and PHP:

:::: tabs

::: tab JSON

```json
{
  "sections": {
    "hero": {
      "type": "visual-hero",
      "settings": {
        "image": "https://example.com/hero.jpg",
        "size": "large"
      }
    },
    "newsletter": {
      "type": "visual-newsletter"
    }
  },
  "order": ["hero", "newsletter"]
}
```

:::

::: tab PHP

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;

return TemplateBuilder::make()
    ->section('hero', 'visual-hero', fn($section) => $section
        ->properties([
            'image' => 'https://example.com/hero.jpg',
            'size' => 'large',
        ])
    )
    ->section('newsletter', 'visual-newsletter')
    ->order(['hero', 'newsletter']);
```

:::

::::

Both produce identical results in the Theme Editor. PHP provides better IDE support and type safety.

## Basic Structure

PHP templates return a `TemplateBuilder` instance configured with sections and their order:

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;

return TemplateBuilder::make()
    ->section('<section-id>', '<section-type>')
    ->section('<section-id>', '<section-type>', fn($section) => $section
        ->properties([...])
        ->blocks([...])
    )
    ->order(['<section-id>', '<section-id>']);
```

## TemplateBuilder API

The `TemplateBuilder` class provides these methods:

| Method                                                        | Description                                 |
| ------------------------------------------------------------- | ------------------------------------------- |
| `make()`                                                      | Create a new template builder instance      |
| `section(string $id, string $type, ?callable $config = null)` | Add a section to the template               |
| `order(array $order)`                                         | Define the rendering order of sections      |
| `id(string $id)`                                              | Set region ID (for header/footer regions)   |
| `name(string $name)`                                          | Set region name (for header/footer regions) |

**Section configuration callback** receives a `BlockBuilder` instance with:

| Method                          | Description                     |
| ------------------------------- | ------------------------------- |
| `properties(array $properties)` | Set section settings/properties |
| `blocks(array $blocks)`         | Add child blocks to the section |

## Example: Simple Homepage

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;

return TemplateBuilder::make()
    ->section('hero', 'visual-hero', fn($section) => $section
        ->properties([
            'image' => 'https://example.com/hero.jpg',
            'size' => 'large',
        ])
    )
    ->section('featured-products', 'visual-featured-products', fn($section) => $section
        ->properties([
            'heading' => 'Featured Products',
            'nb_products' => 8,
        ])
    )
    ->section('newsletter', 'visual-newsletter')
    ->order(['hero', 'featured-products', 'newsletter']);
```

## Using Preset Classes

PHP templates can directly reference preset classes, making configuration reusable and type-safe:

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;
use Themes\MyTheme\Presets\HeroBanner;
use Themes\MyTheme\Presets\CategoryGrid;

return TemplateBuilder::make()
    ->section('hero-banner', HeroBanner::class)
    ->section('category-list', CategoryGrid::class)
    ->section('newsletter', 'visual-newsletter')
    ->order(['hero-banner', 'category-list', 'newsletter']);
```

::: tip Learn More About Presets
See [Presets documentation](../presets.md) to learn about creating standalone preset classes that can be reused across templates.
:::

## Adding Blocks to Sections

Use `PresetBlock` to define child blocks within sections:

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;
use BagistoPlus\Visual\Support\PresetBlock;

return TemplateBuilder::make()
    ->section('hero', 'visual-hero', fn($section) => $section
        ->properties([
            'layout' => 'centered',
            'padding' => 'large',
        ])
        ->blocks([
            PresetBlock::make('heading')
                ->id('hero-title')
                ->settings([
                    'text' => 'Welcome to Our Store',
                    'level' => 1,
                ]),

            PresetBlock::make('paragraph')
                ->id('hero-text')
                ->settings([
                    'text' => 'Discover amazing products',
                ]),

            PresetBlock::make('button')
                ->id('hero-cta')
                ->settings([
                    'text' => 'Shop Now',
                    'style' => 'primary',
                ])
                ->static(), // Lock from editing
        ])
    )
    ->order(['hero']);
```

## Advanced: Nested Blocks

PresetBlock supports deep nesting with the `->children()` method:

```php
PresetBlock::make('container')
    ->id('feature-grid')
    ->settings(['layout' => 'grid', 'columns' => 3])
    ->children([
        PresetBlock::make('container')
            ->children([
                PresetBlock::make('icon')->settings(['icon' => 'star']),
                PresetBlock::make('heading')->settings(['text' => 'Quality']),
            ]),

        PresetBlock::make('container')
            ->children([
                PresetBlock::make('icon')->settings(['icon' => 'truck']),
                PresetBlock::make('heading')->settings(['text' => 'Fast Shipping']),
            ]),
    ]),
```

## Example: Product Page Template

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;
use BagistoPlus\Visual\Support\PresetBlock;

return TemplateBuilder::make()
    ->section('breadcrumbs', 'visual-breadcrumbs')

    ->section('product-info', 'visual-product-information', fn($section) => $section
        ->properties([
            'section_width' => 'container',
            'media_position' => 'left',
            'gap' => 8,
        ])
        ->blocks([
            PresetBlock::make('product-media-gallery')
                ->id('media')
                ->static()
                ->settings([
                    'aspect_ratio' => 'adapt',
                    'zoom' => true,
                ]),

            PresetBlock::make('product-details')
                ->id('details')
                ->static()
                ->children([
                    PresetBlock::make('product-title')->id('title'),
                    PresetBlock::make('product-price')->id('price'),
                    PresetBlock::make('product-variant-picker')->id('variants'),
                    PresetBlock::make('product-buy-buttons')->id('buy-buttons'),
                ]),
        ])
    )

    ->section('product-reviews', 'visual-product-reviews')

    ->order(['breadcrumbs', 'product-info', 'product-reviews']);
```

## Region Templates (Header/Footer)

For header and footer regions, use `->id()` and `->name()` to define the region:

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;
use BagistoPlus\Visual\Support\PresetBlock;

return TemplateBuilder::make()
    ->id('header')
    ->name('Header')

    ->section('announcement-bar', 'visual-announcement-bar')

    ->section('main-header', 'visual-header', fn($section) => $section
        ->properties(['content_width' => 'container'])
        ->blocks([
            PresetBlock::make('logo')
                ->id('logo')
                ->name('Logo'),

            PresetBlock::make('navigation')
                ->id('nav')
                ->name('Navigation'),

            PresetBlock::make('cart')
                ->id('cart')
                ->name('Cart'),
        ])
    )

    ->order(['announcement-bar', 'main-header']);
```

## Using PHP Features

PHP templates can leverage PHP's full power for dynamic configuration:

### Variables and Loops

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;
use BagistoPlus\Visual\Support\PresetBlock;

$featuredCategories = [2, 3, 5, 7];

$categoryBlocks = array_map(
    fn($id) => PresetBlock::make('category')
        ->id("category-{$id}")
        ->settings(['category' => $id]),
    $featuredCategories
);

return TemplateBuilder::make()
    ->section('categories', 'visual-category-list', fn($section) => $section
        ->properties(['heading' => 'Shop by Category'])
        ->blocks($categoryBlocks)
    )
    ->order(['categories']);
```

### Conditionals

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;

$builder = TemplateBuilder::make()
    ->section('hero', 'visual-hero');

// Add promotional banner only during sales
if (config('store.sale_active')) {
    $builder->section('promo-banner', 'visual-promo-banner');
}

$builder->section('products', 'visual-product-list');

return $builder->order(['hero', 'promo-banner', 'products']);
```

### Helper Functions

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;
use BagistoPlus\Visual\Support\PresetBlock;

// Helper function for repeated block patterns
function createFeatureBlock(string $id, string $icon, string $title, string $text): PresetBlock
{
    return PresetBlock::make('container')
        ->id($id)
        ->children([
            PresetBlock::make('icon')->settings(['icon' => $icon]),
            PresetBlock::make('heading')->settings(['text' => $title]),
            PresetBlock::make('paragraph')->settings(['text' => $text]),
        ]);
}

return TemplateBuilder::make()
    ->section('features', 'visual-features', fn($section) => $section
        ->blocks([
            createFeatureBlock('fast-ship', 'truck', 'Fast Shipping', '2-3 day delivery'),
            createFeatureBlock('secure', 'shield', 'Secure Payment', 'Your data is safe'),
            createFeatureBlock('support', 'headset', '24/7 Support', 'Always here to help'),
        ])
    )
    ->order(['features']);
```

## When to Use PHP Templates

| Use PHP Templates When                 | Use JSON/YAML When                    |
| -------------------------------------- | ------------------------------------- |
| Templates are complex with many blocks | Templates are simple                  |
| Need IDE autocomplete and type safety  | Prefer visual editing of raw data     |
| Using preset classes extensively       | Minimal configuration                 |
| Need PHP logic (loops, conditions)     | Static configuration only             |
| Developer-focused workflow             | Designer-focused workflow             |
| Working in an IDE (PhpStorm, VSCode)   | Editing in text editor or admin panel |
| Building reusable template patterns    | One-off page configurations           |

## File Location

PHP templates follow the same location convention as JSON/YAML templates:

```plaintext
resources/views/templates/
├── index.visual.php      # Homepage
├── product.visual.php    # Product page
├── category.visual.php   # Category page
├── cart.visual.php       # Cart page
└── checkout.visual.php   # Checkout page
```

## Best Practices

### ✅ DO

**Use Type Hints**

```php
use BagistoPlus\Visual\Support\TemplateBuilder;
use BagistoPlus\Visual\Support\PresetBlock;

// Clear imports at top of file
```

**Extract Complex Logic**

```php
// Helper functions for readability
function buildCategoryBlocks(): array
{
    return array_map(
        fn($cat) => PresetBlock::make('category')->settings(['id' => $cat]),
        Category::featured()->pluck('id')->toArray()
    );
}
```

**Use Preset Classes**

```php
// Reusable configurations
use Themes\MyTheme\Presets\HeroBanner;

return TemplateBuilder::make()
    ->section('hero', HeroBanner::class);
```

**Document Complex Templates**

```php
return TemplateBuilder::make()
    // Main hero section with CTA
    ->section('hero', 'visual-hero', fn($section) => ...)

    // Featured products grid (shows 8 products)
    ->section('products', 'visual-products', fn($section) => ...)

    ->order(['hero', 'products']);
```

### ❌ DON'T

**Don't Mix Database Queries Directly**

```php
// ❌ Avoid
->properties(['products' => Product::where('featured', true)->get()])

// ✅ Better - use static configuration
->properties(['product_type' => 'featured', 'nb_products' => 8])
```

**Don't Over-Engineer**

```php
// ❌ Too complex
if ($condition1 && $condition2 || (!$condition3 && $condition4)) {
    // ...complex template logic
}

// ✅ Keep it simple - complexity belongs in preset classes
->section('hero', ComplexHeroPreset::class)
```

## Migrating from JSON/YAML

To convert a JSON/YAML template to PHP:

1. **Create new `.visual.php` file** with same name
2. **Import TemplateBuilder**
3. **Convert sections** to `->section()` calls
4. **Convert settings** to `->properties()` arrays
5. **Convert blocks** to `PresetBlock::make()` calls
6. **Add `->order()` at the end**

Example migration:

:::: tabs

::: tab Before (JSON)

```json
{
  "sections": {
    "hero": {
      "type": "visual-hero",
      "settings": {
        "size": "large"
      },
      "blocks": {
        "title": {
          "type": "heading",
          "settings": {
            "text": "Welcome"
          }
        }
      }
    }
  },
  "order": ["hero"]
}
```

:::

::: tab After (PHP)

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;
use BagistoPlus\Visual\Support\PresetBlock;

return TemplateBuilder::make()
    ->section('hero', 'visual-hero', fn($section) => $section
        ->properties(['size' => 'large'])
        ->blocks([
            PresetBlock::make('heading')
                ->id('title')
                ->settings(['text' => 'Welcome']),
        ])
    )
    ->order(['hero']);
```

:::

::::

## Next Steps

- **[JSON & YAML Templates](./json-yaml.md)** - Learn about the declarative alternative
- **[Template Overview](./overview.md)** - Compare all template formats
- **[Presets](../presets.md)** - Create reusable preset classes for templates
- **[Available Templates](./available.md)** - See all default page templates

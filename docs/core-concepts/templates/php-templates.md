# PHP Templates

PHP templates (`.visual.php`) provide a **programmatic, type-safe, IDE-friendly alternative** to JSON and YAML templates. They use the `TemplateBuilder` fluent API to define page structure while maintaining full compatibility with the Theme Editor.

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
        ->settings([
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
        ->settings([...])
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
| `settings(array $settings)`     | Set section settings/properties |
| `blocks(array $blocks)`         | Add child blocks to the section |

## Example: Simple Homepage

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;

return TemplateBuilder::make()
    ->section('hero', 'visual-hero', fn($section) => $section
        ->settings([
            'image' => 'https://example.com/hero.jpg',
            'size' => 'large',
        ])
    )
    ->section('featured-products', 'visual-featured-products', fn($section) => $section
        ->settings([
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
        ->settings([
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
        ->settings([
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
        ->settings(['heading' => 'Shop by Category'])
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

## Choosing Between PHP and JSON/YAML

Both formats are fully interchangeable and produce identical results. Choose based on your **editing experience preference**:

| Choose PHP Templates When You Want    | Choose JSON/YAML When You Want  |
| ------------------------------------- | ------------------------------- |
| IDE autocomplete and IntelliSense     | Simple text file editing        |
| Type safety and error checking        | Direct, minimal syntax          |
| To use PHP variables and loops        | Quick manual editing            |
| To reference preset classes directly  | No PHP knowledge required       |
| Code refactoring tools                | Lightweight configuration files |
| Inline PHP comments and documentation | Visual, declarative structure   |
| Working in PhpStorm/VSCode with PHP   | Editing in any text editor      |

**Both formats:**

- ✅ Are fully customizable by merchants in the Theme Editor
- ✅ Support sections, blocks, settings, and all features
- ✅ Generate the same output
- ✅ Allow the same level of merchant control

## Migrating from JSON/YAML

To convert a JSON/YAML template to PHP:

1. **Create new `.visual.php` file** with same name
2. **Import TemplateBuilder**
3. **Convert sections** to `->section()` calls
4. **Convert settings** to `->settings()` arrays
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
        ->settings(['size' => 'large'])
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

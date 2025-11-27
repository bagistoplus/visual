# Presets

Presets provide pre-configured templates for both blocks and sections, allowing merchants to quickly add common variations without manual configuration. Think of them as "starter templates" or "quick-start options" that appear in the theme editor.

You can define presets in two ways:
- **Inline**: Using the `presets()` method directly in your block/section class
- **Standalone**: Creating dedicated preset classes that extend `Preset`

## What Are Presets?

When merchants add a block or section in the Visual theme editor, they can choose from a list of **presets** - pre-configured variations with different settings, layouts, and child blocks already set up.

**Examples:**
- **Button Block Presets**: "Primary CTA", "Secondary Link", "Outline Button"
- **Hero Section Presets**: "Image Left", "Image Right", "Centered", "Full Width"
- **Gallery Presets**: "2 Column", "3 Column Masonry", "Carousel"

## Why Use Presets?

✅ **Faster setup** - Merchants get started quickly with sensible defaults
✅ **Better UX** - Guide merchants toward common patterns
✅ **Consistency** - Ensure brand-aligned variations
✅ **Discoverability** - Show what's possible with your blocks/sections

## Where Presets Appear

Presets appear in the theme editor when merchants:
1. Click "Add block" within a section
2. Click "Add section" in a template
3. Select a block/section type

The editor shows:
- Preset name
- Description (if provided)
- Icon (if provided)
- Preview image (if provided)
- Grouped by category (if specified)

## Basic Presets (Array Syntax)

The simplest way to define presets is using an array:

### Block Preset

```php
<?php

namespace Themes\YourTheme\Blocks;

use BagistoPlus\Visual\Blocks\SimpleBlock;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Select;

class Button extends SimpleBlock
{
    protected static string $view = 'shop::blocks.button';

    public static function settings(): array
    {
        return [
            Text::make('text', 'Button Text')
                ->default('Click Me'),

            Select::make('style', 'Style')
                ->options([
                    'primary' => 'Primary',
                    'secondary' => 'Secondary',
                    'outline' => 'Outline',
                ])
                ->default('primary'),

            Select::make('size', 'Size')
                ->options([
                    'small' => 'Small',
                    'medium' => 'Medium',
                    'large' => 'Large',
                ])
                ->default('medium'),
        ];
    }

    public static function presets(): array
    {
        return [
            [
                'name' => 'Primary CTA',
                'settings' => [
                    'text' => 'Shop Now',
                    'style' => 'primary',
                    'size' => 'large',
                ],
            ],
            [
                'name' => 'Secondary Link',
                'settings' => [
                    'text' => 'Learn More',
                    'style' => 'secondary',
                    'size' => 'medium',
                ],
            ],
            [
                'name' => 'Outline Button',
                'settings' => [
                    'text' => 'View Details',
                    'style' => 'outline',
                    'size' => 'medium',
                ],
            ],
        ];
    }
}
```

### Section Preset

```php
<?php

namespace Themes\YourTheme\Sections;

use BagistoPlus\Visual\Blocks\BladeSection;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Select;

class Hero extends BladeSection
{
    protected static string $view = 'shop::sections.hero';

    public static function settings(): array
    {
        return [
            Text::make('heading', 'Heading')
                ->default('Welcome'),

            Select::make('layout', 'Layout')
                ->options([
                    'centered' => 'Centered',
                    'left' => 'Image Left',
                    'right' => 'Image Right',
                ])
                ->default('centered'),
        ];
    }

    public static function accepts(): array
    {
        return ['button', 'heading', 'paragraph'];
    }

    public static function presets(): array
    {
        return [
            [
                'name' => 'Centered Hero',
                'settings' => [
                    'heading' => 'Welcome to Our Store',
                    'layout' => 'centered',
                ],
            ],
            [
                'name' => 'Image Left Layout',
                'settings' => [
                    'heading' => 'Discover Our Products',
                    'layout' => 'left',
                ],
            ],
        ];
    }
}
```

## Preset Structure

Each preset (array or class-based) can have these properties:

| Property | Type | Description |
|----------|------|-------------|
| `name` | string | **Required**. Display name in theme editor |
| `description` | string | Optional description shown to merchants |
| `icon` | string | Icon identifier (e.g., `heroicon-o-star`) |
| `category` | string | Group presets into categories |
| `previewImageUrl` | string | URL to preview image |
| `settings` | array | Default settings values (block settings) |
| `children` | array | Default child blocks (for containers/sections) |

### Enhanced Preset with Metadata

```php
public static function presets(): array
{
    return [
        [
            'name' => 'Success Alert',
            'description' => 'Green alert for success messages',
            'icon' => 'heroicon-o-check-circle',
            'category' => 'Alerts',
            'previewImageUrl' => '/images/presets/success-alert.png',
            'settings' => [
                'message' => 'Success! Your action was completed.',
                'type' => 'success',
                'dismissible' => true,
            ],
        ],
        [
            'name' => 'Error Alert',
            'description' => 'Red alert for error messages',
            'icon' => 'heroicon-o-x-circle',
            'category' => 'Alerts',
            'settings' => [
                'message' => 'Error! Something went wrong.',
                'type' => 'error',
                'dismissible' => true,
            ],
        ],
    ];
}
```

## Class-Based Presets (Fluent API)

For more complex presets, use the fluent `Preset` class:

```php
use BagistoPlus\Visual\Support\Preset;

public static function presets(): array
{
    return [
        Preset::make('Success Alert')
            ->description('Green alert for success messages')
            ->icon('heroicon-o-check-circle')
            ->category('Alerts')
            ->previewImageUrl('/images/presets/success-alert.png')
            ->settings([
                'message' => 'Success! Your action was completed.',
                'type' => 'success',
                'dismissible' => true,
            ]),

        Preset::make('Error Alert')
            ->description('Red alert for error messages')
            ->icon('heroicon-o-x-circle')
            ->category('Alerts')
            ->settings([
                'message' => 'Error! Something went wrong.',
                'type' => 'error',
                'dismissible' => true,
            ]),
    ];
}
```

### Benefits of Class-Based Presets

✅ **Readable** - Clear, fluent API
✅ **Type-safe** - IDE autocomplete
✅ **Chainable** - Easy to build complex presets
✅ **Reusable** - Can extend preset classes

## Presets with Child Blocks

Sections and container blocks can include pre-configured child blocks in their presets:

### Section Preset with Child Blocks

```php
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

public static function presets(): array
{
    return [
        Preset::make('Hero with CTA')
            ->description('Full-width hero with heading and call-to-action button')
            ->icon('heroicon-o-photograph')
            ->category('Banners')
            ->settings([
                'layout' => 'centered',
                'background_color' => '#4f46e5',
            ])
            ->blocks([
                PresetBlock::make('heading')
                    ->id('hero-title')
                    ->settings([
                        'text' => 'Welcome to Our Store',
                        'level' => 1,
                    ]),

                PresetBlock::make('paragraph')
                    ->id('hero-subtitle')
                    ->settings([
                        'text' => 'Discover amazing products at great prices',
                    ]),

                PresetBlock::make('button')
                    ->id('hero-cta')
                    ->settings([
                        'text' => 'Shop Now',
                        'style' => 'primary',
                    ]),
            ]),

        Preset::make('Hero with Email Signup')
            ->description('Hero section with email capture form')
            ->icon('heroicon-o-mail')
            ->category('Banners')
            ->settings([
                'layout' => 'centered',
            ])
            ->blocks([
                PresetBlock::make('heading')
                    ->settings(['text' => 'Join Our Newsletter']),

                PresetBlock::make('paragraph')
                    ->settings(['text' => 'Get exclusive deals and updates']),

                PresetBlock::make('email-input'),
                PresetBlock::make('button')
                    ->settings(['text' => 'Subscribe']),
            ]),
    ];
}
```

### Container Block Preset with Children

```php
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

class Columns extends SimpleBlock
{
    protected static string $view = 'shop::blocks.columns';

    public static function blocks(): array
    {
        return ['@theme'];  // Accept any blocks as children
    }

    public static function presets(): array
    {
        return [
            Preset::make('Two Column Features')
                ->description('Two columns with icon, heading, and text')
                ->category('Layouts')
                ->settings(['column_count' => 2])
                ->blocks([
                    // First column
                    PresetBlock::make('container')
                        ->children([
                            PresetBlock::make('icon')
                                ->settings(['icon' => 'heroicon-o-lightning-bolt']),
                            PresetBlock::make('heading')
                                ->settings(['text' => 'Fast Shipping', 'level' => 3]),
                            PresetBlock::make('paragraph')
                                ->settings(['text' => 'Get your order in 2-3 days']),
                        ]),

                    // Second column
                    PresetBlock::make('container')
                        ->children([
                            PresetBlock::make('icon')
                                ->settings(['icon' => 'heroicon-o-shield-check']),
                            PresetBlock::make('heading')
                                ->settings(['text' => 'Secure Checkout', 'level' => 3]),
                            PresetBlock::make('paragraph')
                                ->settings(['text' => 'Your data is safe with us']),
                        ]),
                ]),
        ];
    }
}
```

## PresetBlock API

When defining child blocks in presets, use `PresetBlock::make()`:

```php
PresetBlock::make('button')
    ->id('unique-id')                    // Semantic ID
    ->name('Custom Name')                 // Display name in editor
    ->settings([...])                     // Block settings values
    ->static()                            // Lock from editing
    ->ghost()                             // Data-only, not rendered
    ->repeated()                          // Rendered in loop
    ->children([...])                     // Nested child blocks
    ->order(['id1', 'id2'])              // Rendering order
```

### PresetBlock Methods

| Method | Description |
|--------|-------------|
| `type(string)` | Block type (e.g., 'button', 'heading') |
| `id(string)` | Unique semantic ID for the block |
| `name(string)` | Custom display name in editor |
| `settings(array)` | Block settings values |
| `static(bool)` | Mark as static (non-editable by merchant) |
| `ghost(bool)` | Data-only block (not rendered) |
| `repeated(bool)` | Block is rendered in a loop |
| `children(array)` | Nested child blocks |
| `order(array)` | Order of child block IDs |

## Standalone Preset Classes

For reusable, shareable, or complex presets, you can define them as standalone classes instead of inline arrays. This approach is particularly useful for:

- **Theme-wide presets** shared across multiple blocks/sections
- **Complex configurations** with deep nesting
- **Reusable templates** across different themes
- **Version-controlled presets** maintained separately

### Creating a Standalone Preset Class

Standalone preset classes extend `BagistoPlus\Visual\Support\Preset` and implement two key methods:

```php
<?php

namespace Themes\YourTheme\Presets;

use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

class HeroBanner extends Preset
{
    /**
     * Specify which block or section this preset is for
     */
    protected function getType(): string
    {
        return '@your-theme/hero-section';
    }

    /**
     * Configure the preset
     */
    protected function build(): void
    {
        $this
            ->name('Hero Banner')
            ->description('Full-width hero with heading, text, and CTA')
            ->icon('heroicon-o-photograph')
            ->category('Banners')
            ->settings([
                'layout' => 'centered',
                'background_color' => '#4f46e5',
                'padding' => 'large',
            ])
            ->blocks([
                PresetBlock::make('@your-theme/heading')
                    ->id('hero-title')
                    ->settings([
                        'text' => 'Welcome to Our Store',
                        'level' => 1,
                        'color' => 'white',
                    ]),

                PresetBlock::make('@your-theme/paragraph')
                    ->id('hero-subtitle')
                    ->settings([
                        'text' => 'Discover amazing products at great prices',
                        'color' => 'white',
                    ]),

                PresetBlock::make('@your-theme/button')
                    ->id('hero-cta')
                    ->settings([
                        'text' => 'Shop Now',
                        'style' => 'primary',
                        'size' => 'large',
                    ]),
            ]);
    }
}
```

### Using PresetBlock

Always use `PresetBlock` from the Visual package when defining child blocks in presets:

```php
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

public static function presets(): array
{
    return [
        Preset::make('Hero Banner')
            ->blocks([
                PresetBlock::make('heading')
                    ->settings(['text' => 'Welcome']),

                PresetBlock::make('button')
                    ->settings(['text' => 'Shop Now']),
            ]),
    ];
}
```

The block type identifier can be:
- **Simple name**: `'button'`, `'heading'` (for built-in or theme blocks)
- **Namespaced**: `'@your-theme/button'`, `'@visual-debut/heading'` (explicit namespace)

### Example: Complex Nested Preset

```php
<?php

namespace Themes\YourTheme\Presets;

use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

class FeatureGrid extends Preset
{
    protected function getType(): string
    {
        return '@your-theme/flex-section';
    }

    protected function build(): void
    {
        $this
            ->name('Feature Grid')
            ->description('Three-column feature grid with icons')
            ->category('Content')
            ->settings([
                'layout' => 'grid',
                'columns' => 3,
            ])
            ->blocks([
                // First feature
                PresetBlock::make('@your-theme/container')
                    ->id('feature-1')
                    ->settings(['alignment' => 'center'])
                    ->children([
                        PresetBlock::make('@your-theme/icon')
                            ->settings([
                                'icon' => 'heroicon-o-lightning-bolt',
                                'size' => 'large',
                            ]),
                        PresetBlock::make('@your-theme/heading')
                            ->settings([
                                'text' => 'Fast Shipping',
                                'level' => 3,
                            ]),
                        PresetBlock::make('@your-theme/paragraph')
                            ->settings([
                                'text' => 'Get your order in 2-3 business days',
                            ]),
                    ]),

                // Second feature
                PresetBlock::make('@your-theme/container')
                    ->id('feature-2')
                    ->settings(['alignment' => 'center'])
                    ->children([
                        PresetBlock::make('@your-theme/icon')
                            ->settings([
                                'icon' => 'heroicon-o-shield-check',
                                'size' => 'large',
                            ]),
                        PresetBlock::make('@your-theme/heading')
                            ->settings([
                                'text' => 'Secure Checkout',
                                'level' => 3,
                            ]),
                        PresetBlock::make('@your-theme/paragraph')
                            ->settings([
                                'text' => 'Your payment information is safe',
                            ]),
                    ]),

                // Third feature
                PresetBlock::make('@your-theme/container')
                    ->id('feature-3')
                    ->settings(['alignment' => 'center'])
                    ->children([
                        PresetBlock::make('@your-theme/icon')
                            ->settings([
                                'icon' => 'heroicon-o-heart',
                                'size' => 'large',
                            ]),
                        PresetBlock::make('@your-theme/heading')
                            ->settings([
                                'text' => '100% Satisfaction',
                                'level' => 3,
                            ]),
                        PresetBlock::make('@your-theme/paragraph')
                            ->settings([
                                'text' => 'Love it or return it within 30 days',
                            ]),
                    ]),
            ]);
    }
}
```

### Using Translations in Standalone Presets

Standalone presets can use the `_t()` helper for internationalization:

```php
protected function build(): void
{
    $this
        ->name(_t('sections.hero.presets.banner.name'))
        ->description(_t('sections.hero.presets.banner.description'))
        ->settings([
            'heading' => _t('sections.hero.presets.banner.heading'),
            'text' => _t('sections.hero.presets.banner.text'),
        ])
        ->blocks([
            PresetBlock::make('@theme/button')
                ->settings([
                    'text' => _t('sections.hero.presets.banner.cta_text'),
                ]),
        ]);
}
```

### Organizing Preset Classes

Store standalone preset classes in a dedicated directory:

```
your-theme/
└── src/
    └── Presets/
        ├── HeroBanner.php
        ├── FeatureGrid.php
        ├── TestimonialCarousel.php
        └── ClassicFooter.php
```

### When to Use Standalone Preset Classes

| Use Standalone Classes When | Use Inline presets() Method When |
|------------------------------|----------------------------------|
| Preset is complex with deep nesting | Preset is simple (2-3 properties) |
| Preset is reused across themes | Preset is specific to one block |
| Preset needs to be reused in other presets (`::asChild()`) | Preset is only used inline |
| Preset requires translations | Preset uses static values |
| Preset is version-controlled separately | Preset is quick variations |
| Multiple presets share logic | Variations are straightforward |

### Complete Example: E-commerce Footer

```php
<?php

namespace Themes\YourTheme\Presets;

use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

class ClassicFooter extends Preset
{
    protected function getType(): string
    {
        return '@your-theme/footer-section';
    }

    protected function build(): void
    {
        $this
            ->name('Classic E-commerce Footer')
            ->description('Four-column footer with links, newsletter, and social icons')
            ->category('Footers')
            ->settings([
                'background_color' => '#1f2937',
                'text_color' => 'white',
            ])
            ->blocks([
                // About column
                PresetBlock::make('@your-theme/footer-column')
                    ->id('about-column')
                    ->settings(['title' => 'About Us'])
                    ->children([
                        PresetBlock::make('@your-theme/link')
                            ->settings(['text' => 'Our Story', 'url' => '/about']),
                        PresetBlock::make('@your-theme/link')
                            ->settings(['text' => 'Careers', 'url' => '/careers']),
                        PresetBlock::make('@your-theme/link')
                            ->settings(['text' => 'Press', 'url' => '/press']),
                    ]),

                // Support column
                PresetBlock::make('@your-theme/footer-column')
                    ->id('support-column')
                    ->settings(['title' => 'Customer Support'])
                    ->children([
                        PresetBlock::make('@your-theme/link')
                            ->settings(['text' => 'Contact Us', 'url' => '/contact']),
                        PresetBlock::make('@your-theme/link')
                            ->settings(['text' => 'Shipping Info', 'url' => '/shipping']),
                        PresetBlock::make('@your-theme/link')
                            ->settings(['text' => 'Returns', 'url' => '/returns']),
                    ]),

                // Shop column
                PresetBlock::make('@your-theme/footer-column')
                    ->id('shop-column')
                    ->settings(['title' => 'Shop'])
                    ->children([
                        PresetBlock::make('@your-theme/link')
                            ->settings(['text' => 'New Arrivals', 'url' => '/new']),
                        PresetBlock::make('@your-theme/link')
                            ->settings(['text' => 'Best Sellers', 'url' => '/bestsellers']),
                        PresetBlock::make('@your-theme/link')
                            ->settings(['text' => 'Sale', 'url' => '/sale']),
                    ]),

                // Newsletter column
                PresetBlock::make('@your-theme/footer-column')
                    ->id('newsletter-column')
                    ->settings(['title' => 'Stay Connected'])
                    ->children([
                        PresetBlock::make('@your-theme/paragraph')
                            ->settings(['text' => 'Subscribe to our newsletter for updates']),
                        PresetBlock::make('@your-theme/email-input')
                            ->settings(['placeholder' => 'Your email']),
                        PresetBlock::make('@your-theme/button')
                            ->settings(['text' => 'Subscribe', 'style' => 'primary']),
                        PresetBlock::make('@your-theme/social-icons')
                            ->settings([
                                'icons' => ['facebook', 'instagram', 'twitter'],
                            ]),
                    ]),
            ]);
    }
}
```

### Reusing Presets with `::asChild()`

Standalone preset classes can be reused as child blocks within other presets using the `::asChild()` method. This enables composition and eliminates duplication when the same preset configuration needs to appear in multiple places.

#### Basic Usage

```php
<?php

namespace Themes\YourTheme\Presets;

use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

// Define a reusable preset
class ProductCard extends Preset
{
    protected function getType(): string
    {
        return '@your-theme/product-card';
    }

    protected function build(): void
    {
        $this
            ->name('Product Card with Overlay')
            ->category('Products')
            ->blocks([
                PresetBlock::make('@your-theme/product-image')
                    ->settings(['aspect_ratio' => 'square']),

                PresetBlock::make('@your-theme/product-title')
                    ->settings(['size' => 'large']),

                PresetBlock::make('@your-theme/product-price')
                    ->settings(['show_compare' => true]),
            ]);
    }
}

// Reuse it in another preset
class ProductGrid extends Preset
{
    protected function getType(): string
    {
        return '@your-theme/product-grid';
    }

    protected function build(): void
    {
        $this
            ->name('Featured Products Grid')
            ->settings(['columns' => 4])
            ->blocks([
                PresetBlock::make('@your-theme/heading')
                    ->settings(['text' => 'Featured Products']),

                // Reuse ProductCard preset as a child block
                ProductCard::asChild()
                    ->id('product-card')
                    ->static()
                    ->repeated(),
            ]);
    }
}
```

#### How `::asChild()` Works

The `::asChild()` method:
1. **Instantiates the preset class** and runs its `build()` method
2. **Extracts the configuration**: settings, child blocks, and name
3. **Determines the block type** from `getType()` method (or parameter)
4. **Returns a `PresetBlock` instance** with the preset's configuration
5. **Allows further chaining** with methods like `->id()`, `->static()`, `->repeated()`

#### Specifying Block Type

The block type is determined in order of priority:

```php
// 1. Using getType() method (recommended)
class CategoryCard extends Preset
{
    protected function getType(): string
    {
        return '@your-theme/category-card';
    }
}

CategoryCard::asChild()  // Type: @your-theme/category-card


// 2. Passing type as parameter (override getType)
CategoryCard::asChild('@different-theme/card')  // Type: @different-theme/card


// 3. No type causes exception
class BadPreset extends Preset
{
    // No getType() defined!
}

BadPreset::asChild()  // ❌ Throws LogicException
```

#### Real-World Example: Category Grid

```php
<?php

namespace Themes\YourTheme\Presets;

use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

// Reusable category card preset
class CategoryCardOverlay extends Preset
{
    protected function getType(): string
    {
        return '@your-theme/category-card';
    }

    protected function build(): void
    {
        $this
            ->name('Category Card with Overlay')
            ->blocks([
                // Category image with hover zoom
                PresetBlock::make('@your-theme/category-image')
                    ->settings([
                        'aspect_ratio' => 'square',
                        'hover_zoom' => true,
                    ]),

                // Overlay container
                PresetBlock::make('@your-theme/container')
                    ->settings([
                        'is_overlay' => true,
                        'background_color' => 'rgba(0, 0, 0, 0.35)',
                    ])
                    ->children([
                        PresetBlock::make('@your-theme/category-name')
                            ->settings([
                                'color' => 'white',
                                'alignment' => 'center',
                            ]),
                    ]),
            ]);
    }
}

// Category grid section that reuses the card
class CategoryGrid extends Preset
{
    protected function getType(): string
    {
        return '@your-theme/category-list';
    }

    protected function build(): void
    {
        $this
            ->name('Category Grid')
            ->settings([
                'layout' => 'grid',
                'columns' => 4,
            ])
            ->blocks([
                PresetBlock::make('@your-theme/heading')
                    ->settings(['text' => 'Shop by Category']),

                // Reuse CategoryCardOverlay preset
                CategoryCardOverlay::asChild()
                    ->id('static-category-card')
                    ->static()      // Prevent editing
                    ->repeated()    // Render multiple times
                    ->name('Category Card'),

                // Ghost blocks for example categories
                PresetBlock::make('@your-theme/category-data')
                    ->ghost()
                    ->settings(['category_id' => 2]),

                PresetBlock::make('@your-theme/category-data')
                    ->ghost()
                    ->settings(['category_id' => 3]),
            ]);
    }
}
```

#### Benefits of `::asChild()`

✅ **DRY Principle** - Define preset once, reuse everywhere
✅ **Consistency** - Same configuration across different sections
✅ **Maintainability** - Update preset in one place, changes propagate
✅ **Composition** - Build complex presets from simpler ones
✅ **Type Safety** - IDE autocomplete for preset class names

#### When to Use `::asChild()`

| Use `::asChild()` When | Use Regular `PresetBlock::make()` When |
|------------------------|----------------------------------------|
| Configuration is reused in multiple presets | Configuration is unique to one preset |
| Complex nested structure appears repeatedly | Simple single block |
| Need to maintain consistency across presets | One-off customization |
| Preset already exists as standalone class | Inline definition is simpler |

## Advanced Features

### Static Blocks in Presets

Mark blocks as **static** to prevent merchants from editing or removing them:

```php
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

Preset::make('Product Card')
    ->blocks([
        PresetBlock::make('product-image')
            ->static(),  // Merchants can't remove this

        PresetBlock::make('product-title')
            ->static(),  // Always present

        PresetBlock::make('product-price')
            ->static(),  // Required

        PresetBlock::make('button')
            ->settings(['text' => 'Add to Cart']),  // Editable
    ]),
```

### Ghost Blocks

Ghost blocks store data but aren't rendered directly:

```php
PresetBlock::make('settings')
    ->ghost()
    ->settings([
        'api_key' => 'default-key',
        'endpoint' => 'https://api.example.com',
    ]),
```

### Repeated Blocks

Mark blocks that will be rendered in a loop:

```php
PresetBlock::make('slide')
    ->repeated()
    ->settings(['image' => '/default-slide.jpg']),
```

### Nested Children (Deep Nesting)

Create complex nested structures:

```php
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

Preset::make('Tabbed Product Grid')
    ->blocks([
        PresetBlock::make('tabs')
            ->children([
                PresetBlock::make('tab')
                    ->name('Featured Products')
                    ->children([
                        PresetBlock::make('product-grid')
                            ->settings(['collection' => 'featured'])
                            ->children([
                                PresetBlock::make('product-card')->repeated(),
                                PresetBlock::make('product-card')->repeated(),
                                PresetBlock::make('product-card')->repeated(),
                            ]),
                    ]),

                PresetBlock::make('tab')
                    ->name('New Arrivals')
                    ->children([
                        PresetBlock::make('product-grid')
                            ->settings(['collection' => 'new'])
                            ->children([
                                PresetBlock::make('product-card')->repeated(),
                                PresetBlock::make('product-card')->repeated(),
                            ]),
                    ]),
            ]),
    ]),
```

## Real-World Examples

### Example 1: Testimonials Section

```php
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

public static function presets(): array
{
    return [
        Preset::make('Three Testimonials')
            ->description('Grid of three customer testimonials')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->category('Social Proof')
            ->settings([
                'heading' => 'What Our Customers Say',
                'layout' => 'grid',
            ])
            ->blocks([
                PresetBlock::make('testimonial')
                    ->settings([
                        'quote' => 'Amazing product! Highly recommend.',
                        'author' => 'John Doe',
                        'rating' => 5,
                    ]),

                PresetBlock::make('testimonial')
                    ->settings([
                        'quote' => 'Great quality and fast shipping.',
                        'author' => 'Jane Smith',
                        'rating' => 5,
                    ]),

                PresetBlock::make('testimonial')
                    ->settings([
                        'quote' => 'Excellent customer service!',
                        'author' => 'Bob Johnson',
                        'rating' => 5,
                    ]),
            ]),

        Preset::make('Carousel Testimonials')
            ->description('Scrolling carousel of testimonials')
            ->icon('heroicon-o-arrow-path')
            ->category('Social Proof')
            ->settings([
                'heading' => 'Customer Reviews',
                'layout' => 'carousel',
            ])
            ->blocks([
                PresetBlock::make('testimonial')->repeated(),
                PresetBlock::make('testimonial')->repeated(),
                PresetBlock::make('testimonial')->repeated(),
                PresetBlock::make('testimonial')->repeated(),
            ]),
    ];
}
```

### Example 2: Gallery Section

```php
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

public static function presets(): array
{
    return [
        Preset::make('2 Column Grid')
            ->category('Galleries')
            ->settings(['columns' => 2])
            ->blocks([
                PresetBlock::make('image'),
                PresetBlock::make('image'),
                PresetBlock::make('image'),
                PresetBlock::make('image'),
            ]),

        Preset::make('3 Column Masonry')
            ->category('Galleries')
            ->settings(['columns' => 3, 'style' => 'masonry'])
            ->blocks([
                PresetBlock::make('image'),
                PresetBlock::make('image'),
                PresetBlock::make('image'),
                PresetBlock::make('image'),
                PresetBlock::make('image'),
                PresetBlock::make('image'),
            ]),

        Preset::make('Image Carousel')
            ->category('Galleries')
            ->settings(['style' => 'carousel', 'autoplay' => true])
            ->blocks([
                PresetBlock::make('image')->repeated(),
                PresetBlock::make('image')->repeated(),
                PresetBlock::make('image')->repeated(),
            ]),
    ];
}
```

### Example 3: Call-to-Action Variations

```php
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

public static function presets(): array
{
    return [
        Preset::make('Simple CTA')
            ->category('CTAs')
            ->settings(['layout' => 'simple'])
            ->blocks([
                PresetBlock::make('heading')
                    ->settings(['text' => 'Ready to get started?'])
                    ->static(),

                PresetBlock::make('button')
                    ->settings(['text' => 'Get Started', 'style' => 'primary']),
            ]),

        Preset::make('Split CTA with Image')
            ->category('CTAs')
            ->settings(['layout' => 'split'])
            ->blocks([
                PresetBlock::make('image')
                    ->settings(['image' => '/default-cta.jpg'])
                    ->static(),

                PresetBlock::make('heading')
                    ->settings(['text' => 'Join thousands of happy customers']),

                PresetBlock::make('paragraph')
                    ->settings(['text' => 'Start your journey today']),

                PresetBlock::make('button')
                    ->settings(['text' => 'Sign Up Free']),
            ]),
    ];
}
```

## Best Practices

### ✅ DO

**Use Meaningful Names**
```php
Preset::make('Primary Call-to-Action Button')  // ✅ Clear
Preset::make('Button 1')                       // ❌ Vague
```

**Provide Helpful Descriptions**
```php
->description('Large blue button for main CTAs')  // ✅ Helpful
->description('A button')                         // ❌ Not helpful
```

**Organize with Categories**
```php
->category('Banners')      // Group related presets
->category('Social Proof')
->category('CTAs')
```

**Set Sensible Defaults**
```php
->settings([
    'text' => 'Shop Now',           // ✅ Meaningful default
    'style' => 'primary',
    'size' => 'large',
])
```

**Include Preview Images**
```php
->previewImageUrl('/images/presets/hero-centered.png')  // Visual preview
```

**Provide Common Use Cases**
```php
// ✅ Cover common scenarios
'Centered Hero', 'Image Left Hero', 'Image Right Hero'

// ❌ Too specific
'Hero for Homepage', 'Hero for About Page'
```

### ❌ DON'T

**Don't Create Too Many Presets**
```php
// ❌ Overwhelming
public static function presets(): array
{
    return array_fill(0, 50, [...]);  // 50 presets!
}

// ✅ Focused selection
public static function presets(): array
{
    return [
        // 3-7 most common variations
    ];
}
```

**Don't Use Generic Names**
```php
// ❌ Generic
'Preset 1', 'Preset 2', 'Preset 3'

// ✅ Descriptive
'Primary CTA', 'Secondary Link', 'Outline Button'
```

**Don't Forget Preset for Default State**
```php
// ✅ Include a "Default" or "Basic" preset
Preset::make('Default Button')
    ->settings([...])  // Matches block defaults
```

**Organize Standalone Presets Properly**
```php
// ✅ Dedicated Presets directory
your-theme/src/Presets/HeroBanner.php
your-theme/src/Presets/FeatureGrid.php

// ❌ Mixed with blocks/sections
your-theme/src/Blocks/HeroBannerPreset.php
```

## Categories

Group related presets using the `category` property:

```php
use BagistoPlus\Visual\Support\Preset;

public static function presets(): array
{
    return [
        // Banners category
        Preset::make('Hero Banner')->category('Banners'),
        Preset::make('Promotion Banner')->category('Banners'),

        // Social Proof category
        Preset::make('Testimonial Grid')->category('Social Proof'),
        Preset::make('Review Carousel')->category('Social Proof'),

        // CTAs category
        Preset::make('Newsletter Signup')->category('CTAs'),
        Preset::make('Product Launch')->category('CTAs'),
    ];
}
```

The theme editor will:
- Group presets by category
- Show categories as collapsible sections
- Display uncategorized presets first

## Preview Images

Add visual previews to help merchants choose:

```php
use BagistoPlus\Visual\Support\Preset;

Preset::make('Hero with Image Left')
    ->previewImageUrl('/images/presets/hero-image-left.png')
```

**Tips for preview images:**
- Use 16:9 aspect ratio (e.g., 800x450px)
- Show realistic content, not placeholders
- Keep file size under 100KB
- Use PNG or JPEG format
- Store in `public/images/presets/`

## Next Steps

- **[Block Schema](/building-theme/adding-blocks/block-schema)** - Complete guide to block settings and schema
- **[Container Blocks](/building-theme/adding-blocks/container-blocks)** - Blocks that accept children
- **[Sections Overview](/building-theme/adding-sections/overview)** - Building sections
- **[Settings Types](/core-concepts/settings/types)** - Available setting field types

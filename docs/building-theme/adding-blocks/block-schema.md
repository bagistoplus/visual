# Block Schema

The block schema defines how your block behaves, what settings it exposes, and whether it can accept child blocks. This guide covers all schema configuration options.

## Schema Components

A block's schema consists of three main methods:

- `settings()` - Define merchant-editable settings
- `presets()` - Provide pre-configured block templates
- `blocks()` - For container blocks, specify which child blocks are accepted

## Settings

Settings allow merchants to customize block appearance and content without code.

### Basic Settings Example

```php
public static function settings(): array
{
    return [
        Text::make('title', 'Title')
            ->default('Default Title')
            ->required(),

        Textarea::make('description', 'Description')
            ->default('Enter description here')
            ->maxLength(500),

        Image::make('image', 'Image'),

        Select::make('alignment', 'Text Alignment')
            ->options([
                'left' => 'Left',
                'center' => 'Center',
                'right' => 'Right',
            ])
            ->default('left'),
    ];
}
```

### Available Setting Types

| Setting Type | Description | Example Use |
|--------------|-------------|-------------|
| `Text` | Single-line text input | Headings, labels, names |
| `Textarea` | Multi-line text input | Descriptions, longer content |
| `RichText` | WYSIWYG editor | Formatted content |
| `Number` | Numeric input | Counts, sizes, limits |
| `Range` | Slider input | Opacity, spacing |
| `Color` | Color picker | Background, text colors |
| `Image` | Image upload | Photos, icons, logos |
| `Video` | Video upload/URL | Video content |
| `Link` | URL builder | Links, CTAs |
| `Select` | Dropdown selection | Predefined options |
| `Radio` | Radio buttons | Exclusive choices |
| `Checkbox` | True/false toggle | Enable/disable features |
| `Icon` | Icon picker | Icons from icon libraries |
| `Product` | Product selector | Link to products |
| `Category` | Category selector | Link to categories |
| `Page` | CMS page selector | Link to pages |

### Setting Options

All settings support common options:

```php
Text::make('id', 'Label')
    ->default('default value')          // Default value
    ->required()                         // Make required
    ->placeholder('Enter text...')      // Placeholder text
    ->help('Help text for merchants')   // Help text
    ->validation('required|max:100')    // Laravel validation rules
```

### Conditional Settings

Show/hide settings based on other settings:

```php
Select::make('type', 'Block Type')
    ->options(['image' => 'Image', 'video' => 'Video']),

Image::make('image', 'Image')
    ->showIf('type', 'image'),  // Only show if type is 'image'

Video::make('video', 'Video')
    ->showIf('type', 'video'),  // Only show if type is 'video'
```

## Presets

Presets provide pre-configured block templates that merchants can quickly add from the theme editor. They're "quick-start options" with predefined settings and child blocks.

> **📖 See [Presets Guide](/core-concepts/presets)** for comprehensive documentation on creating presets for blocks and sections, including advanced features like nested children, categories, and preview images.

### Basic Example

```php
public static function presets(): array
{
    return [
        [
            'name' => 'Primary Button',
            'settings' => [
                'text' => 'Shop Now',
                'style' => 'primary',
                'size' => 'large',
            ],
        ],
        [
            'name' => 'Secondary Button',
            'settings' => [
                'text' => 'Learn More',
                'style' => 'secondary',
                'size' => 'medium',
            ],
        ],
    ];
}
```

When merchants add this block, they'll see these preset options to choose from.

For more advanced preset features including icons, categories, preview images, and nested children, see the **[Presets Guide](/core-concepts/presets)**.

## Container Blocks (Accepting Children)

Container blocks can accept child blocks, enabling nesting. Define which blocks can be nested using the `blocks()` method:

### Accepting Specific Blocks

```php
public static function blocks(): array
{
    return [
        'button',           // Only Button blocks
        'image',            // Only Image blocks
        'heading',          // Only Heading blocks
    ];
}
```

### Accepting All Theme Blocks

```php
public static function blocks(): array
{
    return [
        '@theme',  // Accept all theme blocks
    ];
}
```

### Mixed Block Types

```php
public static function blocks(): array
{
    return [
        '@theme',          // All theme blocks
        'button',          // Explicitly include Button
        'custom-block',    // Custom block type
    ];
}
```

## Complete Schema Example

Here's a complete block showing all schema features:

```php
<?php

namespace Themes\YourTheme\Blocks;

use BagistoPlus\Visual\Block\BladeBlock;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Textarea;
use BagistoPlus\Visual\Settings\Select;
use BagistoPlus\Visual\Settings\Color;
use BagistoPlus\Visual\Settings\Image;

class CallToAction extends BladeBlock
{
    protected static string $view = 'shop::blocks.call-to-action';

    public static function name(): string
    {
        return 'Call to Action';
    }

    public static function settings(): array
    {
        return [
            Text::make('heading', 'Heading')
                ->default('Ready to get started?')
                ->required(),

            Textarea::make('description', 'Description')
                ->default('Join thousands of satisfied customers')
                ->maxLength(200),

            Select::make('layout', 'Layout')
                ->options([
                    'centered' => 'Centered',
                    'split' => 'Split',
                ])
                ->default('centered'),

            Image::make('background_image', 'Background Image')
                ->showIf('layout', 'split'),

            Color::make('background_color', 'Background Color')
                ->default('#4f46e5'),

            Color::make('text_color', 'Text Color')
                ->default('#ffffff'),
        ];
    }

    public static function blocks(): array
    {
        return [
            'button',  // Accept button blocks for CTAs
        ];
    }

    public static function maxBlocks(): int
    {
        return 2;  // Max 2 buttons
    }

    public static function presets(): array
    {
        return [
            [
                'name' => 'Newsletter Signup',
                'settings' => [
                    'heading' => 'Subscribe to our newsletter',
                    'description' => 'Get the latest updates and offers',
                    'layout' => 'centered',
                ],
            ],
            [
                'name' => 'Product Launch',
                'settings' => [
                    'heading' => 'New Collection Available',
                    'description' => 'Discover our latest products',
                    'layout' => 'split',
                ],
            ],
        ];
    }
}
```

## Schema Validation

Settings support Laravel validation rules:

```php
Text::make('email', 'Email')
    ->validation('required|email'),

Number::make('quantity', 'Quantity')
    ->validation('required|integer|min:1|max:100'),

Image::make('logo', 'Logo')
    ->validation('required|image|max:2048'),  // Max 2MB
```

## Best Practices

✅ **Group related settings**: Keep related settings together
✅ **Provide defaults**: Every setting should have a sensible default
✅ **Use help text**: Explain non-obvious settings
✅ **Limit options**: Don't overwhelm with too many choices
✅ **Validation**: Validate user input appropriately
✅ **Conditional display**: Hide irrelevant settings based on context
✅ **Useful presets**: Provide practical, common-use presets

## Next Steps

- **[Presets](/core-concepts/presets)**: Complete guide to creating block and section presets
- **[Static vs Dynamic Blocks](/building-theme/adding-blocks/static-vs-dynamic-blocks)**: Learn when blocks are static or dynamic
- **[Rendering Blocks](/building-theme/adding-blocks/rendering-blocks)**: How to render blocks in views
- **[Container Blocks](/building-theme/adding-blocks/container-blocks)**: Deep dive into blocks that accept children

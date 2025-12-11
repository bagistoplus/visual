# Block Attributes

Block classes in Bagisto Visual can define a number of attributes that control how they are identified, rendered, and displayed in the editor.

## type

The block type identifier used to reference this block in sections and the Visual Editor.

```php
protected static string $type = '@awesome-theme/product-card';
```

or

```php
public static function type(): string
{
    return '@awesome-theme/product-card';
}
```

**Default:**
If omitted, the type is generated from the class name using kebab-case.
`ProductCard` becomes `product-card`

**Recommended format:** `@vendor/block-type`

It's recommended to include a vendor prefix (e.g., `@awesome-theme/product-card`) to avoid collisions, especially when blocks come from packages. This ensures uniqueness across different themes and packages.

## name

Display name in the block picker.

```php
protected static string $name = 'Product Card';
```

or

```php
public static function name(): string
{
    return __('Product Card');
}
```

**Default:**
Derived from the class name, title-cased.
Example: `ProductCard` becomes "Product Card".

## view

Blade view used to render the block.

```php
protected static string $view = 'shop::blocks.product-card';
```

**Default:**

- For theme blocks: `shop::blocks.{slug}`
- For non-theme blocks: `blocks.{slug}`

## wrapper

HTML wrapper using a simplified Emmet-style syntax. When a wrapper is defined, the necessary attributes for the Visual Editor are injected automatically.

```php
protected static string $wrapper = 'div.product-card>div.card-content';
```

Results in:

```html
<div class="product-card" data-block="generated-visual-id">
  <div class="card-content">
    <!-- Block blade view content -->
  </div>
</div>
```

**Default:** `div`

### Without a wrapper

When no wrapper is defined, you must manually add the editor attributes to the root element in your Blade view so the block can be handled in the Visual Editor:

```blade
<div {{ $block->editor_attributes }} class="product-card">
  <div class="card-content">
    <!-- Block content -->
  </div>
</div>
```

The editor_attributes helper injects the necessary data attributes required for the Visual Editor to identify and interact with the block.

## description

Short description shown in the block picker in the theme editor.

```php
protected static string $description = 'Displays a product with image, title, and price.';
```

or

```php
public static function description(): string
{
    return __('Displays a product with image, title, and price.');
}
```

## category

Groups blocks together in the block picker of the Visual Editor.

```php
protected static string $category = 'Product';
```

or

```php
public static function category(): string
{
    return __('Product');
}
```

Blocks with the same category will be grouped together in the block picker, making it easier for merchants to find related blocks.

Common categories: `Content`, `Product`, `Layout`, `Media`, `Forms`, `Marketing`

## icon

Icon displayed in the block picker in the Visual Editor. Must be a raw SVG string.

```php
protected static string $icon = '<svg>...</svg>';
```

or

```php
public static function icon(): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>';
}
```

**Default:** A default block icon is used if not specified.

## previewImageUrl

Path to a preview image, relative to the `public/` directory or a full URL.

Displayed in the block picker in the theme editor.

```php
protected static string $previewImageUrl = 'images/blocks/product-card-preview.png';
```

or

```php
public static function previewImageUrl(): string
{
    return url('images/blocks/product-card-preview.png');
}
```

**Default:**

- Theme: `vendor/themes/awesome-theme/assets/images/blocks/{slug}-preview.png`
- Non-theme: `images/blocks/{slug}-preview.png`

## previewDescription

Optional text shown below the preview image.

```php
protected static string $previewDescription = 'Shows product information in a card layout.';
```

or

```php
public static function previewDescription(): string
{
    return __('Shows product information in a card layout.');
}
```

## presets

Defines pre-configured variations of the block that merchants can choose from when adding the block. Presets allow you to provide quick-start templates with predefined settings.

```php
use BagistoPlus\Visual\Support\Preset;

public static function presets(): array
{
    return [
        Preset::make('Primary Button')
            ->description('Large primary call-to-action button')
            ->settings([
                'text' => 'Shop Now',
                'style' => 'primary',
                'size' => 'large',
            ]),

        Preset::make('Secondary Button')
            ->description('Standard secondary button')
            ->settings([
                'text' => 'Learn More',
                'style' => 'secondary',
                'size' => 'medium',
            ]),

        Preset::make('Outline Button')
            ->description('Minimal outline style button')
            ->settings([
                'text' => 'View Details',
                'style' => 'outline',
                'size' => 'medium',
            ]),
    ];
}
```

Presets support:

- `name()` - Display name in the preset picker
- `description()` - Optional description text
- `settings()` - Default settings values
- `icon()` - Optional icon (SVG string)
- `category()` - Optional category for grouping
- `previewImageUrl()` - Optional preview image URL

For comprehensive documentation on creating presets, see the [Presets Guide](../../core-concepts/presets.md).

## settings

Defines the configurable fields for the block that appear in the Visual Editor's settings panel.

```php
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Textarea;
use BagistoPlus\Visual\Settings\Color;
use BagistoPlus\Visual\Settings\Icon;

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
```

For all available setting types and their options, see the [Settings documentation](../../core-concepts/settings/types.md).

## private

Controls whether the block appears in the general block picker. Private blocks are hidden from the main picker but can be made available to specific parent blocks or sections through explicit accepts listing.

```php
protected static bool $private = true;
```

**Default:** `false` (block is public and appears in pickers)

### Visibility Rules

Private blocks follow strict visibility rules:

1. **Hidden from General Picker**: Never appear in the main block picker
2. **Explicit Accepts Required**: Only visible when explicitly listed in a parent's `accepts` array
3. **Wildcards Don't Include Private**: Patterns like `'*'` or `'@vendor/*'` do NOT make private blocks visible

**Example:**

```php
// Private block - only usable within specific contexts
class TabItem extends SimpleBlock
{
    protected static bool $private = true;
}

// Section that explicitly accepts the private block
class Tabs extends SimpleSection
{
    protected static array $accepts = [
        '@awesome-theme/tab-item',  // Explicit - TabItem will appear
    ];
}

// Section with wildcard - private block still hidden
class Container extends SimpleSection
{
    protected static array $accepts = ['*'];  // TabItem NOT included
}
```

Use private blocks for:
- Component parts (like tab items, accordion panels)
- Blocks that only make sense in specific contexts
- Internal/structural blocks not meant for direct use

## accepts

For container blocks that can accept child blocks, defines which block types can be nested inside.

```php
protected static array $accepts = [
    '@awesome-theme/heading',
    '@awesome-theme/button',
    '@awesome-theme/image',
];
```

### Wildcards:

Accept all blocks:

```php
protected static array $accepts = ['*'];
```

Accept all blocks from a specific vendor/package:

```php
protected static array $accepts = ['@awesome-theme/*'];
```

### Using block classes:

You can also reference blocks using their PHP class names:

```php
use Themes\AwesomeTheme\Blocks\Heading;
use Themes\AwesomeTheme\Blocks\Button;

protected static array $accepts = [
    Heading::class,
    Button::class,
];
```

**Default:** `[]` (does not accept children)

### Rendering Children

Render child blocks in your block view using `@children`:

```blade
<div {{ $block->editor_attributes }} class="card">
    <div class="card-header">
        <h3>{{ $block->settings->title }}</h3>
    </div>
    <div class="card-body">
        @children
    </div>
</div>
```

---

Next: [Static Blocks](./static-blocks.md)

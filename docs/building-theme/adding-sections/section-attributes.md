# Section Attributes

Section classes in Bagisto Visual can define a number of attributes that control how they are identified, rendered, and displayed in the editor.

## type

The block type identifier used to reference this section in templates and the Visual Editor.

```php
protected static string $type = '@awesome-theme/announcement-bar';
```

or

```php
public static function type(): string
{
    return '@awesome-theme/announcement-bar';
}
```

**Default:**
If omitted, the type is generated from the class name using kebab-case.
`AnnouncementBar` becomes `announcement-bar`

**Recommended format:** `@vendor/section-type`

It's recommended to include a vendor prefix (e.g., `@awesome-theme/announcement-bar`) to avoid collisions, especially when sections come from packages. This ensures uniqueness across different themes and packages.

This is the identifier used in templates:

```json
{
  "sections": {
    "my-announcement": {
      "type": "@awesome-theme/announcement-bar"
    }
  }
}
```

## name

Display name in the section editor.

```php
protected static string $name = 'Announcement Bar';
```

or

```php
public static function name(): string
{
    return __('Announcement Bar');
}
```

**Default:**
Derived from the class name, title-cased.
Example: `AnnouncementBar` becomes "Announcement Bar".

## view

Blade view used to render the section.

```php
protected static string $view = 'shop::sections.announcement-bar';
```

**Default:**

- For theme sections: `shop::sections.{slug}`
- For non-theme sections: `sections.{slug}`

## wrapper

HTML wrapper using a simplified Emmet-style syntax. When a wrapper is defined, the necessary attributes for the Visual Editor are injected automatically.

```php
protected static string $wrapper = 'section#announcement-bar>div.container';
```

Results in:

```html
<section id="announcement-bar" data-block="generated-visual-id">
  <div class="container">
    <!-- Section blade view content -->
  </div>
</section>
```

**Default:** `section`

### Without a wrapper

When no wrapper is defined, you must manually add the editor attributes to the root element in your Blade view so the section can be handled in the Visual Editor:

```blade
<section {{ $section->editor_attributes }}>
  <div class="container">
    <!-- Section content -->
  </div>
</section>
```

The editor_attributes helper injects the necessary data attributes required for the Visual Editor to identify and interact with the section.

## description

Short description shown in the section picker in the theme editor.

```php
protected static string $description = 'Used for banners or alerts.';
```

or

```php
public static function description(): string
{
    return __('Used for banners or alerts.');
}
```

## category

Groups sections together in the section picker of the Visual Editor.

```php
protected static string $category = 'Marketing';
```

or

```php
public static function category(): string
{
    return __('Marketing');
}
```

Sections with the same category will be grouped together in the section picker, making it easier for merchants to find related sections.

Common categories: `Header`, `Hero`, `Marketing`, `Products`, `Content`, `Footer`, `Forms`

## icon

Icon displayed in the section picker in the Visual Editor. Must be a raw SVG string.

```php
protected static string $icon = '<svg>...</svg>';
```

or

```php
public static function icon(): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
    </svg>';
}
```

**Default:** A default stack icon is used if not specified.

## previewImageUrl

Path to a preview image, relative to the `public/` directory or a full URL.

Displayed in the section picker in the theme editor

```php
protected static string $previewImageUrl = 'images/sections/announcement-bar-preview.png';
```

or

```php
public static function previewImageUrl(): string
{
    return url('images/sections/announcement-bar-preview.png');
}
```

**Default:**

- Theme: `vendor/themes/awesome-theme/assets/images/sections/{slug}-preview.png`
- Non-theme: `images/sections/{slug}-preview.png`

## previewDescription

Optional text shown below the preview image.

```php
protected static string $previewDescription = 'Displays a rotating announcement banner.';
```

or

```php
public static function previewDescription(): string
{
    return __('Displays a rotating announcement banner.')
}
```

## presets

Defines pre-configured variations of the section that merchants can choose from when adding the section. Presets allow you to provide quick-start templates with predefined settings and blocks.

```php
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

public static function presets(): array
{
    return [
        Preset::make('Centered Hero')
            ->description('Full-width hero with centered content')
            ->settings([
                'heading' => 'Welcome to Our Store',
                'layout' => 'centered',
                'background_color' => '#4f46e5',
            ])
            ->blocks([
                PresetBlock::make('@awesome-theme/heading')
                    ->id('hero-title')
                    ->settings(['text' => 'Welcome to Our Store', 'level' => 1]),

                PresetBlock::make('@awesome-theme/button')
                    ->id('hero-cta')
                    ->settings(['text' => 'Shop Now', 'style' => 'primary']),
            ]),

        Preset::make('Image Left Layout')
            ->description('Hero with image on the left side')
            ->settings([
                'heading' => 'Discover Our Products',
                'layout' => 'left',
            ]),
    ];
}
```

Presets support:

- `name()` - Display name in the preset picker
- `description()` - Optional description text
- `settings()` - Default settings values
- `children` - Pre-configured child blocks using PresetBlock
- `icon()` - Optional icon (SVG string)
- `category()` - Optional category for grouping
- `previewImageUrl()` - Optional preview image URL

For comprehensive documentation on creating presets with nested blocks, categories, and advanced features, see the [Presets Guide](../../core-concepts/presets.md).

## accepts

Defines which block types can be added to this section.

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

**Default:** `['*']`

Render blocks in your section view using `@children`:

```blade
<div class="section-content">
    @children
</div>
```

## settings

Defines the configurable fields for the section that appear in the Visual Editor's settings panel.

```php
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Color;
use BagistoPlus\Visual\Settings\Link;

public static function settings(): array
{
    return [
        Text::make('heading', 'Heading')
            ->default('Welcome to Our Store'),

        Color::make('background_color', 'Background Color')
            ->default('#4f46e5'),

        Link::make('cta_link', 'Call to Action Link'),
    ];
}
```

### Dynamic Defaults

Settings can use **dynamic sources** to populate defaults from runtime context:

```php
public static function settings(): array
{
    return [
        Text::make('productName', 'Product Name')
            ->default('@product.name'),

        Number::make('price', 'Price')
            ->default('@product.price'),

        Image::make('image', 'Product Image')
            ->default('@product.base_image.url'),
    ];
}
```

The `@path.to.value` syntax resolves from page context (controller variables) or parent-shared data. **[Learn more about Dynamic Sources](/core-concepts/dynamic-sources)**

For all available setting types and their options, see the [Settings documentation](../../core-concepts/settings/types.md).

## enabledOn

Specifies which **templates** and **regions** this section can be added to.

```php
protected static array $enabledOn = [
    'templates' => ['index', 'product', 'account/*'],
    'regions' => ['header', 'footer'],
];
```

- `templates` - Array of template types where this section can be added (optional)
- `regions` - Array of region IDs where this section can be added (optional)

Both keys are optional. You can specify templates only, regions only, or both.

**Examples:**

Restrict to specific templates only:

```php
protected static array $enabledOn = [
    'templates' => ['index', 'product'],
];
```

Restrict to specific regions only:

```php
protected static array $enabledOn = [
    'regions' => ['header', 'footer'],
];
```

Restrict to both templates and regions:

```php
protected static array $enabledOn = [
    'templates' => ['index', 'product'],
    'regions' => ['header'],
];
```

Wildcards are supported in templates:

- `'account/*'` matches account/profile, account/addresses, etc.
- `'*'` matches all templates

**Default:** No restrictions (can be added anywhere)

## disabledOn

Specifies which **templates** and **regions** this section should be excluded from.

```php
protected static array $disabledOn = [
    'templates' => ['checkout', 'auth/*'],
    'regions' => ['sidebar'],
];
```

- `templates` - Array of template types to exclude this section from (optional)
- `regions` - Array of region IDs to exclude this section from (optional)

Both keys are optional. You can exclude from templates only, regions only, or both.

**Example:**

Exclude from checkout templates:

```php
protected static array $disabledOn = [
    'templates' => ['checkout'],
];
```

`disabledOn` takes priority over `enabledOn` when both are specified.

**Default:** No exclusions

---

Next: [Writing the Section View](./writing-section-view.md)

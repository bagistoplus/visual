---
outline: 3
---

# Setting Types

Bagisto Visual includes a range of **setting types** like text inputs, color pickers, toggle switches, dropdowns, and more.
These ready-made types make it easy for developers to offer flexible customization options in sections, blocks, and themes — all through the visual editor.

## Standard Setting Attributes

Every setting type supports a few standard attributes:

| Attribute | Description                                 | Required |
| :-------- | :------------------------------------------ | :------- |
| `id`      | The unique identifier for the setting.      | Yes      |
| `label`   | The text label shown to merchants.          | Yes      |
| `default` | The default value assigned to the field.    | No       |
| `info`    | Optional helper text shown below the field. | No       |

## Available Setting Types

### Text

Single-line text input. Useful for headings, labels, and short descriptions.

In addition to the standard attributes, Text type settings have the following attribute:

| Attribute     | Description                        | Required |
| :------------ | :--------------------------------- | :------- |
| `placeholder` | A placeholder value for the input. | No       |

```php
use BagistoPlus\Visual\Settings\Text;

public static function settings(): array
{
    return [
        Text::make('title', 'Heading')
            ->default('Welcome to our store')
            ->placeholder('Enter heading here...'),
    ];
}
```

In Blade:

```blade
@if ($section->settings->text)
    <h1>{{ $section->settings->text }}</h1>
@endif
```

<SettingPreview image="/setting-text.png" title="Text setting type preview"/>

---

### Textarea

Multiline text input. Useful for longer descriptions or rich content areas.

In addition to the standard attributes, Textarea type settings have the following attribute:

| Attribute     | Description                           | Required |
| :------------ | :------------------------------------ | :------- |
| `placeholder` | A placeholder value for the textarea. | No       |

```php
use BagistoPlus\Visual\Settings\Textarea;

public static function settings(): array
{
    return [
        Textarea::make('description', 'Store Description')
            ->default('This is your store description.')
            ->placeholder('Write something about your store...'),
    ];
}
```

In Blade:

```blade
@if ($section->settings->description)
    <p>{{ $section->settings->description }}</p>
@endif
```

<SettingPreview image="/setting-textarea.png" title="Textarea setting type preview"/>

---

### Checkbox

Simple true/false toggle. Useful for enabling or disabling features.

Checkbox settings do not have any additional attributes beyond the standard attributes, but support an alternative switch variant for a different visual style.

```php
use BagistoPlus\Visual\Settings\Checkbox;

public static function settings(): array
{
    return [
        Checkbox::make('show_banner', 'Show Banner')
            ->default(true),
    ];
}
```

#### Switch Variant

Use the `asSwitch()` method to display the checkbox as a toggle switch instead of a standard checkbox:

```php
Checkbox::make('enable_feature', 'Enable Feature')
    ->asSwitch()
    ->default(false),
```

In Blade:

```blade
@if ($section->settings->show_banner)
    <div class="banner">
        <!-- Banner content here -->
    </div>
@endif
```

<SettingPreview image="/setting-checkbox.png" title="Checkbox setting type preview"/>

::: info
If `default` is unspecified, it defaults to `false`.
:::

---

### Radio

Single option selection via radio buttons. Useful for choosing between predefined mutually exclusive options.

In addition to the standard attributes, Radio type settings have the following attribute:

| Attribute | Description                                                                                | Required |
| :-------- | :----------------------------------------------------------------------------------------- | :------- |
| `options` | Array of options formatted as `'value' => 'Label'` or `[ 'value' => ..., 'label' => ... ]` | Yes      |

```php
use BagistoPlus\Visual\Settings\Radio;

public static function settings(): array
{
    return [
        Radio::make('alignment', 'Alignment')
            ->options([
                'left' => 'Left',
                'center' => 'Center',
                'right' => 'Right',
            ])
            ->default('center'),
    ];
}
```

Alternative format:

```php
Radio::make('alignment', 'Alignment')
    ->options([
        ['value' => 'left', 'label' => 'Left'],
        ['value' => 'center', 'label' => 'Center'],
        ['value' => 'right', 'label' => 'Right'],
    ])
    ->default('center');
```

In Blade:

```blade
<div class="text-{{ $section->settings->alignment }}">
    <!-- Content -->
</div>
```

<SettingPreview image="/setting-radio.png" title="Radio setting type preview"/>

::: info
If default is unspecified, then the first option is selected by default.
:::

---

### Select

Dropdown selection. Useful for choosing from a list of predefined options.

In addition to the standard attributes, Select type settings have the following attribute:

| Attribute | Description                                                                                | Required |
| :-------- | :----------------------------------------------------------------------------------------- | :------- |
| `options` | Array of options formatted as `'value' => 'Label'` or `[ 'value' => ..., 'label' => ... ]` | Yes      |

```php
use BagistoPlus\Visual\Settings\Select;

public static function settings(): array
{
    return [
        Select::make('layout', 'Layout Style')
            ->options([
                'grid' => 'Grid',
                'list' => 'List',
            ])
            ->default('grid'),
    ];
}
```

Alternative format:

```php
Select::make('layout', 'Layout Style')
    ->options([
        ['value' => 'grid', 'label' => 'Grid'],
        ['value' => 'list', 'label' => 'List'],
    ])
    ->default('grid');
```

In Blade:

```blade
<div class="layout-{{ $section->settings->layout }}">
    <!-- Content displayed based on layout type -->
</div>
```

<SettingPreview image="/setting-select.png" title="Select setting type preview"/>

::: info
If `default` is unspecified, then the first option is selected by default.
:::

---

### Range

Numeric slider input. Useful for values like spacing, number of columns, or padding.

In addition to the standard attributes, Range type settings have the following attributes:

| Attribute | Description                     | Required |
| :-------- | :------------------------------ | :------- |
| `min`     | Minimum value allowed           | Yes      |
| `max`     | Maximum value allowed           | Yes      |
| `step`    | Increment steps (default `1`)   | No       |
| `unit`    | Optional label for unit display | No       |

```php
use BagistoPlus\Visual\Settings\Range;

public static function settings(): array
{
    return [
        Range::make('columns', 'Number of Columns')
            ->min(1)
            ->max(6)
            ->step(1)
            ->unit('cols')
            ->default(3),
    ];
}
```

In Blade:

```blade
<div class="grid-cols-{{ $section->settings->columns }}">
    <!-- Grid content with dynamic columns -->
</div>
```

<SettingPreview image="/setting-range.png" title="Range setting type preview"/>

::: info
If default is unspecified, it defaults to the minimum value.
:::

---

### Number

Single-line numeric input. Useful for entering quantities, prices, padding, margins, and other number-based configurations.

In addition to the standard attributes, Number type settings have the following attributes:

| Attribute     | Description                        | Required |
| :------------ | :--------------------------------- | :------- |
| `placeholder` | A placeholder value for the input. | No       |
| `min`         | Minimum value allowed              | No       |
| `max`         | Maximum value allowed              | No       |
| `step`        | Increment steps (default `1`)      | No       |

```php
use BagistoPlus\Visual\Settings\Number;

public static function settings(): array
{
    return [
        Number::make('max_width', 'Max Width')
            ->min(320)
            ->max(1920)
            ->step(10)
            ->default(1200)
            ->placeholder('Enter a maximum width...'),
    ];
}
```

In Blade:

```blade
<div style="max-width: {{ $section->settings->max_width }}px;">
    <!-- Content -->
</div>
```

<SettingPreview image="/setting-number.png" title="Number setting type preview"/>

---

### Spacing

Four-sided spacing input. Useful for controlling padding and margin values independently for each side (top, right, bottom, left).

The visual editor provides an intuitive interface with four individual number inputs and a link toggle button that allows merchants to sync all sides to the same value when enabled.

In addition to the standard attributes, Spacing type settings have the following attributes:

| Attribute | Description                    | Required |
| :-------- | :----------------------------- | :------- |
| `min`     | Minimum value for each side    | No       |
| `max`     | Maximum value for each side    | No       |

```php
use BagistoPlus\Visual\Settings\Spacing;

public static function settings(): array
{
    return [
        Spacing::make('padding', 'Padding')
            ->min(0)
            ->max(100)
            ->default(['top' => 16, 'right' => 16, 'bottom' => 16, 'left' => 16]),

        Spacing::make('margin', 'Margin')
            ->min(-50)
            ->max(100),
    ];
}
```

In Blade:

```blade
<div style="padding: {{ $section->settings->padding->top }}px
                      {{ $section->settings->padding->right }}px
                      {{ $section->settings->padding->bottom }}px
                      {{ $section->settings->padding->left }}px;">
    <!-- Content -->
</div>
```

<SettingPreview image="/setting-spacing.png" title="Spacing setting type preview"/>

::: info
- Default values: All sides default to `0` if not specified
- The visual editor includes a link toggle to sync all sides
- Negative values are supported for margins (set appropriate min value)
:::

---

### Color

Color picker input. Useful for background colors, text colors, or brand-related customization.

When accessing a color setting inside Blade, the value is either:

- `null` (if no color selected)
- an instance of [`matthieumastadenis\couleur\Color`](https://github.com/matthieumastadenis/couleur)

```php
use BagistoPlus\Visual\Settings\Color;

public static function settings(): array
{
    return [
        Color::make('background_color', 'Background Color')
            ->default('#92400e'),
    ];
}
```

In Blade:

```blade
@if ($section->settings->background_color)
    <div style="background-color: {{ $section->settings->background_color }};">
        <!-- Content -->
    </div>
@endif
```

<SettingPreview image="/setting-color.png" title="Color setting type preview"/>

---

### Link

URL input field. Useful for buttons, banners, images, and any elements that need a hyperlink.

The Link setting allows the merchant to either:

- Enter a custom URL manually
- **Select a resource** (like a Product, Category, or CMS Page) directly from the store

```php
use BagistoPlus\Visual\Settings\Link;

public static function settings(): array
{
    return [
        Link::make('cta_link', 'Call to Action Link')
            ->default('/'),
    ];
}
```

In Blade:

```blade
@if ($section->settings->cta_link)
    <a href="{{ $section->settings->cta_link }}">
        Browse Collection
    </a>
@endif
```

<SettingPreview image="/setting-link.png" title="Link setting type preview"/>

---

### Image

Image picker input. Useful for banners, logos, thumbnails, or any visual element.

The merchant can:

- Upload a new image
- Pick an existing image from the media library

```php
use BagistoPlus\Visual\Settings\Image;

public static function settings(): array
{
    return [
        Image::make('banner_image', 'Banner Image'),
    ];
}
```

In Blade:

```blade
@if ($section->settings->banner_image)
    <img src="{{ $section->settings->banner_image }}" alt="Banner" />
@endif
```

<SettingPreview image="/setting-image.png" title="Image setting type preview"/>

---

### Category

Dropdown selector input. Useful for allowing the merchant to select a category from the store catalog.

When accessing a category setting inside Blade, the value is either:

- `null` (if no category selected)
- an instance of the `Webkul\Category\Models\Category` model

```php
use BagistoPlus\Visual\Settings\Category;

public static function settings(): array
{
    return [
        Category::make('featured_category', 'Featured Category'),
    ];
}
```

In Blade:

```blade
@if ($section->settings->featured_category)
    <x-shop::category-card :category="$section->settings->featured_category" />
@endif
```

<SettingPreview image="/setting-category.png" title="Category setting type preview"/>

---

### Product

Dropdown selector input. Useful for allowing the merchant to select a product from the store catalog.

When accessing a product setting inside Blade, the value is either:

- `null` (if no product selected)
- an instance of the `Webkul\Product\Models\Product` model

```php
use BagistoPlus\Visual\Settings\Product;

public static function settings(): array
{
    return [
        Product::make('featured_product', 'Featured Product'),
    ];
}
```

In Blade:

```blade
@if ($section->settings->featured_product)
    <x-shop::product-card :product="$section->settings->featured_product" />
@endif
```

<SettingPreview image="/setting-product.png" title="Product setting type preview"/>

---

### CmsPage

Dropdown selector input. Useful for allowing the merchant to select a CMS page from the store.

When accessing a CMS page setting inside Blade, the value is either:

- `null` (if no page selected)
- an instance of the `Webkul\CMS\Models\CmsPage` model

```php
use BagistoPlus\Visual\Settings\CmsPage;

public static function settings(): array
{
    return [
        CmsPage::make('policy_page', 'Policy Page'),
    ];
}
```

In Blade:

```blade
@if ($section->settings->policy_page)
    <a href="{{ route('cms.page', $section->settings->policy_page->url_key) }}">
        {{ $section->settings->policy_page->page_title }}
    </a>
@endif
```

<SettingPreview image="/setting-page.png" title="Page setting type preview"/>

---

### RichText

WYSIWYG rich-text editor input. Useful for inserting formatted content like paragraphs, lists, links, and formatted text.

RichText fields support the following basic formatting options:

- Bold
- Italic
- Underline
- Paragraph
- Headings
- Bullet list
- Ordered list

In addition to the standard attributes, RichText type settings have the following attribute:

| Attribute | Description                                   | Required |
| :-------- | :-------------------------------------------- | :------- |
| `inline`  | Whether the editor should be rendered inline. | No       |

```php
use BagistoPlus\Visual\Settings\RichText;

public static function settings(): array
{
    return [
        RichText::make('content', 'Content Block'),

        RichText::make('highlight', 'Highlight Text')
            ->inline(),
    ];
}
```

In Blade:

```blade
@if ($section->settings->content)
    <div class="richtext">
        {!! $section->settings->content !!}
    </div>
@endif

@if ($section->settings->highlight)
    <span class="highlight">
        {!! $section->settings->highlight !!}
    </span>
@endif
```

<SettingPreview image="/setting-richtext.png" title="RichText setting type preview"/>

---

### Font

Font picker input. Useful for allowing merchants to select fonts from the [Bunny Fonts](https://fonts.bunny.net/) catalog.

Font settings allow the merchant to select a web-safe font that is automatically loaded from Bunny Fonts.

```php
use BagistoPlus\Visual\Settings\Font;

public static function settings(): array
{
    return [
        Font::make('heading_font', 'Heading Font')->default('roboto'),
    ];
}
```

<SettingPreview image="/setting-font.png" title="Font setting type preview"/>

In Blade:

```blade
@if ($section->settings->heading_font)
    <h1 style="font-family: '{{ $section->settings->heading_font }}', sans-serif;">
        <!-- Content with dynamic font -->
    </h1>
@endif
```

This will render as:

```html
<h1 style="font-family: 'Roboto', sans-serif;">
  <!-- Content with dynamic font -->
</h1>
```

Additionally, you may use the following snippet to render any resources necessary to load the font

```blade
@pushOnce('styles')
  {{ $section->settings->heading_font->toHtml() }}
@endPushOnce
```

Will render:

```html
<link rel="preconnect" href="https://fonts.bunny.net" />
<link href="https://fonts.bunny.net/css?family=roboto:" rel="stylesheet" />
```

---

### Icon

Icon selection input. Useful for allowing merchants to choose an icon from the installed Blade Icon sets.

Bagisto Visual ships with the [Lucide](https://lucide.dev/) icon set pre-installed by default.
Developers can manually install and configure additional Blade Icons (like Heroicons, Tabler Icons, etc.) if needed.

```php
use BagistoPlus\Visual\Settings\Icon;

public static function settings(): array
{
    return [
        Icon::make('button_icon', 'Button Icon'),
    ];
}
```

In Blade:

```blade
@if ($section->settings->button_icon)
    <!-- Using @svg Blade directive -->
    @svg($section->settings->button_icon, ['class' => 'w-6 h-6'])

    <!-- Alternatively, manual rendering -->
    {!! $section->settings->button_icon->render(['class' => 'w-6 h-6']) !!}
@endif
```

<SettingPreview image="/setting-icon.png" title="Icon setting type preview"/>

---

### ColorScheme

The `ColorScheme` setting allows merchants to choose a color scheme defined by the theme.
Each color scheme is a named palette of colors (e.g., background, text, primary, etc.) and is defined in the theme using a `ColorSchemeGroup`.

This setting is typically used in **section settings** to let the merchant apply predefined styles to specific areas of the storefront.

---

> This setting **does not define color schemes** — it only lets the merchant pick one from those defined in the theme.

---

```php
use BagistoPlus\Visual\Settings\ColorScheme;

public static function settings(): array
{
    return [
        ColorScheme::make('color_scheme', 'Color Scheme')
            ->default('default'),
    ];
}
```

This creates a dropdown in the visual editor populated with all color schemes defined by the theme’s `ColorSchemeGroup`.

**In Blade:**

The recommended way to use the selected color scheme in your view is:

```blade
<div {!! $section->settings->color_scheme->attributes() !!}>
    <!-- Section content -->
</div>
```

This will output:

```html
<div data-color-scheme="light">
  <!-- ... -->
</div>
```

This is used to scope the color schemes tokens to this block.

<SettingPreview image="/setting-color-scheme.png" title="Color scheme setting type preview"/>

---

### ColorSchemeGroup

The `ColorSchemeGroup` setting type allows theme developers to define a **set of named color schemes** that merchants can reuse across multiple sections.
Each color scheme is a collection of color roles (like `background`, `text`, `primary`, etc.) and can be selected using a [ColorScheme](#colorscheme) setting within any section.

This is typically defined once in your theme’s `config/settings.php` and is **editable by the merchant**.

- Acts as the **central registry of available color schemes**
- Enables consistency in color use across sections
- Can be extended or modified by the merchant in the theme editor

#### Usage in `config/settings.php`

```php
use BagistoPlus\Visual\Settings\ColorSchemeGroup;

return [
    ColorSchemeGroup::make('color_schemes', 'Color Schemes')
        ->schemes([
            'light' => [
                'label' => 'Light',
                'tokens' => [
                    'background' => '#ffffff',
                    'on-background' => '#111827',
                    'primary' => '#4f46e5',
                    '...'
                ],
            ],
            'dark' => [
                'label' => 'Dark',
                'tokens' => [
                    'background' => '#111827',
                    'on-background' => '#f9fafb',
                    'primary' => '#6366f1',
                    '...'
                ],
            ],
        ]),
];
```

<SettingPreview image="/setting-color-scheme-group.png" title="Color scheme group setting type preview"/>

#### Scheme Format

Each scheme is an array with:

- A unique **key** (e.g., `light`, `dark`, `brand`)
- A **label** (used in the dropdown)
- A `tokens` array with color roles (e.g., `background`, `on-background`, `primary`, etc.)

```php
[
    'light' => [
        'label' => 'Light',
        'tokens' => [
            'background' => '#ffffff',
            'on-background' => '#111827',
            'primary' => '#4f46e5',
        ]
    ]
]
```

#### Behavior in the Theme Editor

- Merchants can **add, edit, or remove** color schemes directly from the theme editor
- They can:
  - Rename schemes
  - Change color values
- Any section using a `ColorScheme` setting will automatically reflect the updated list

<SettingPreview image="/setting-edit-color-scheme.png" title="Edit color scheme setting type preview"/>

#### Blade Usage

The `ColorSchemeGroup` setting is not accessed directly in sections.
However, **theme developers must output CSS variables** for every scheme so that sections using `ColorScheme` can style themselves accordingly.

Each scheme should be scoped using a `data-color-scheme` attribute:

```html
<style>
  [data-color-scheme='light'] {
    --color-background: #ffffff;
    --color-on-background-text: #111827;
    --color-primary: #4f46e5;
  }
</style>
```

#### 💡 Suggestion: Include Brand Color Shades

For primary or accent colors, it’s recommended to output shades (like Tailwind’s colors).

```blade
<style>
[data-color-scheme="light"] {
    --color-background: #ffffff;
    --color-on-background: #111827;
    --color-primary: #4f46e5;
    --color-primary-50: ...;
    --color-primary-100: ...;
    ...
    --color-primary-950: ...;
}
</style>
```

These can be used in components via var(--color-primary-500) for consistent, scalable design.

If you are using tailwindcss, you could just use utility classes:

```blade
<section class="bg-background text-on-background">
  <button class="px-3 py-2 bg-primary text-on-primary hover:bg-primary-600 active:bg-promary-700">
    Primary button
  </button>
</section>
```

---

Bagisto Visual provides a helper method to generate the full CSS output automatically from the theme's color schemes:

```blade
{{-- layouts/default.blade.php --}}
<style>
  @foreach ($theme->settings->color_schemes as $scheme)
    [data-color-scheme="{{ $scheme->id }}"] {
      {!! $scheme->outputCssVars() !!}
    }
  @endforeach
</style>
```

- This will loop over every scheme
- Output all defined colors as `--color-*` tokens
- Automatically generate shades for brand color roles

#### Notes

- Only one `ColorSchemeGroup` should be defined per theme
- Sections do **not** define color schemes — they reference them via the `ColorScheme` setting
- If no `ColorSchemeGroup` is defined, `ColorScheme` fields will not be functional

-> [Read more about color schemes](../../building-theme/best-practices/styling.md)

---

### Header

Visual divider or label inside settings groups. Useful for organizing complex settings panels into meaningful sections.

Unlike other settings, the Header type:

- Does **not** require an `id`
- Only needs a **label** (text that will be displayed as a title)
- **Does not produce any setting data** (not available inside Blade)

```php
use BagistoPlus\Visual\Settings\Header;
use BagistoPlus\Visual\Settings\Color;
use BagistoPlus\Visual\Settings\Text;

public static function settings(): array
{
    return [
        Header::make('Design Options'),

        Color::make('background_color', 'Background Color')
            ->default('#ffffff'),

        Color::make('text_color', 'Text Color')
            ->default('#000000'),

        Header::make('Content Settings'),

        Text::make('heading', 'Heading Text')
            ->default('Welcome to our store'),

        Text::make('subheading', 'Subheading Text')
            ->default('Discover amazing products'),
    ];
}
```

> **Note:** Header settings are **only used inside the theme editor** to visually group fields.
> They are not available inside Blade templates.

<SettingPreview image="/setting-header.png" title="Header setting type preview"/>

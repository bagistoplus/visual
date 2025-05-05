# Section Attributes

Section classes in Bagisto Visual can define a number of attributes that control how they are identified, rendered, and displayed in the editor.

## slug

The unique identifier of the section.

```php
protected static string $slug = 'announcement-bar';
```

or

```php
public static function slug(): string
{
    return 'announcement-bar';
}
```

**Default:**
If omitted, the slug is generated from the class name using kebab-case.
`AnnouncementBar` → `announcement-bar`

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
Derived from the slug, title-cased.
`announcement-bar` → `Announcement Bar`

## view

Blade view used to render the section.

```php
protected static string $view = 'shop::sections.announcement-bar';
```

**Default:**

- For theme sections: `shop::sections.{slug}`
- For non-theme sections: `sections.{slug}`

## wrapper

HTML wrapper using a simplified Emmet-style syntax.

```php
protected static string $wrapper = 'section#announcement-bar>div.container';
```

Results in:

```html
<section id="announcement-bar">
  <div class="container">
    <!-- Section blade view content -->
  </div>
</section>
```

**Default:** `section`

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

## maxBlocks

Limits how many blocks this section can have.

```php
protected static int $maxBlocks = 4;
```

**Default:** 16

## default

Initial settings and blocks shown when the section is added.

```php
protected static array $default = [
    'settings' => [...],
    'blocks' => [...],
];
```

or

```php
public static function default(): array
{
    return [
        'settings' => [...],
        'blocks' => [...]
    ]
}
```

## enabledOn

Specifies which **template types** this section can be added to.

By default, a section can be added to any template.
If needed, you can restrict a section to only be available on specific templates.

```php
protected static array $enabledOn = ['index', 'product', 'account/*'];
```

This determines where the section appears in the “Add Section” menu of the Visual Editor.

You may use `*` as a wildcard to match a group of templates:

- `'auth/*'` matches auth/login, auth/register, etc.
- `'account/*'` matches account/profile, account/addresses, etc.

**Default:** ['*']

→ [See available template types](../../core-concepts/templates/available.md)

## disabledOn

Specifies which **template types** this section should be hidden from.

This is the inverse of `enabledOn`.
If a template is listed in `disabledOn`, the section will be **excluded from that template**, even if it would normally be available (for example, via `enabledOn`).

```php
protected static array $disabledOn = ['checkout', 'auth/*', 'account/*'];
```

**Default:** []

Use this to prevent a section from being added where it doesn’t make sense (like checkout or account pages)

You can use both `enabledOn` and `disabledOn` — `disabledOn` always takes priority

---

Next: [Defining Settings and Blocks](./defining-section-schema.md)

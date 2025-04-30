# Sections

Sections are **modular, reusable building blocks** that define the content and layout of storefront pages in Bagisto Visual.
Each section is built as a **PHP class** and paired with a **Blade view** for rendering.

Sections expose configurable settings and optional blocks, making it easy for both developers and merchants to customize storefront pages dynamically.

## Why Sections Matter

- **For Developers**: Create flexible, reusable UI components that can be configured without modifying the code.
- **For Merchants**: Easily customize and rearrange sections using the Bagisto Visual Theme Editor, without needing a developer.

Sections empower a truly dynamic storefront experience where layout and content can evolve over time.

## Anatomy of a Section

A section is made up of three main parts:

- A **view**, responsible for displaying the content. Typically built using Blade templates, the view controls the HTML and structure seen by customers.
- A **set of configurable settings**, which define how merchants can customize the section’s behavior and appearance without touching the code.
- **Blocks**, a set of customizable items that merchants can arrange in the Theme Editor to personalize the section, such as creating multiple categories in a list or slides in a carousel.

Settings allow merchants to change things like text, colors, images, or layout options directly from the Theme Editor. Blocks make sections dynamic and flexible, allowing merchants to customize the internal structure of the section itself.

Sections are designed to be simple for developers to create and highly flexible for merchants to personalize.

## Section Directory Structure

```plaintext
/theme/
├── src/Sections/
│   ├── AnnouncementBar.php
│   ├── CategoryList.php
├── resources/views/sections/
│   ├── announcement-bar.blade.php
│   └── category-list.blade.php
```

- `src/Sections/` contains the PHP section classes.
- `resources/views/sections/` contains the corresponding Blade templates.

## Basic Section Example

### PHP Section Class

```php
namespace Themes\YourTheme\Sections;

use Bagisto\Visual\Section\BladeSection;
use Bagisto\Visual\Settings\Text;
use Bagisto\Visual\Settings\Link;
use Bagisto\Visual\Settings\Color;

class AnnouncementBar extends BladeSection
{
    protected static string $view = 'shop::sections.announcement-bar';

    public static function settings(): array
    {
        return [
            Text::make('text', 'Banner text')
                ->default('Welcome to our store!'),

            Link::make('link', 'Banner link'),

            Color::make('background_color', 'Background color')
                ->default('#4f46e5'),

            Color::make('text_color', 'Text color')
                ->default('#ffffff'),
        ];
    }
}
```

---

### Blade View Example

```blade
<div class="announcement-bar" style="background-color: {{ $section->settings->background_color }}; color: {{ $section->settings->text_color }}">
    <a href="{{ $section->settings->link ?? '#' }}">
        {{ $section->settings->text }}
    </a>
</div>
```

✅ A `$section` object representing the section is automatically injected into each Blade view. Settings can be accessed via `$section->settings`.

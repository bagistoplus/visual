# Sections

Sections are **containers that compose blocks into cohesive layouts** on storefront pages. They define structure and accept blocks that provide the content.

Each section is built as a **PHP class** and paired with a **Blade view** for rendering.

## Sections vs Blocks

Understanding the relationship between sections and blocks is key to v2:

- **Blocks** are atomic, reusable components (buttons, images, testimonials)
- **Sections** are containers that arrange blocks into layouts (hero, product grid, features)

Think of blocks as LEGO bricks and sections as the base plates you build on.

## Why Sections Matter

- **For Developers**: Create flexible containers that accept and arrange blocks without hardcoding content.
- **For Merchants**: Build custom layouts by adding, removing, and arranging blocks within sections using the Theme Editor.

Sections empower merchants to build custom page layouts without code.

## Anatomy of a Section

A section consists of:

- A **view** - Blade template defining the section's layout structure
- **Settings** - Section-level configuration (colors, layout options, etc.)
- **Block schema** - Defines which blocks the section accepts and how many
- **Rendering logic** - How accepted blocks are arranged and displayed

Sections provide structure; blocks fill that structure with content.

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

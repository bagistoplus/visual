# Rendering a Section

Each section has an associated Blade view that defines how its settings and blocks are displayed. The view is automatically injected with a `$section` object.

## Accessing Data in the View

In your Blade file (e.g. `resources/views/sections/announcement-bar.blade.php`), use:

- `$section->settings` — for static fields defined in `settings()`
- `$section->blocks` — for repeatable items defined in `blocks()`

## Example: Rotating Announcement Bar

This example uses Alpine.js to rotate through multiple announcements:

### Section Class

Let's consider the following section:

```php
use BagistoPlus\Visual\Sections\BladeSection;
use BagistoPlus\Visual\Sections\Block;
use BagistoPlus\Visual\Settings\Color;
use BagistoPlus\Visual\Settings\Text;

class AnnouncementBar extends BladeSection
{
    protected static string $view = 'shop::sections.announcement-bar';

    public static function settings(): array
    {
        return [
            Color::make('background_color', 'Background')->default('#facc15'),
            Color::make('text_color', 'Text Color')->default('#000000'),
        ];
    }

    public static function blocks(): array
    {
        return [
            Block::make('announcement')
                ->settings([
                    Text::make('text', 'Text')->default('Message content here'),
                ]),
        ];
    }

    public static function default(): array
    {
        return [
            'settings' => [
                'background_color' => '#facc15',
                'text_color' => '#000000',
            ],
            'blocks' => [
                ['type' => 'announcement', 'text' => 'Free shipping on all orders'],
                ['type' => 'announcement', 'text' => 'Extended returns until Jan 31'],
            ],
        ];
    }
}
```

### Blade view

This will be the corresponding view

```blade
<section
    class="py-3"
    style="background-color: {{ $section->settings->background_color }}; color: {{ $section->settings->text_color }}"
    x-data="{ current: 0, total: {{ count($section->blocks) }} }"
>
    <div class="container mx-auto flex items-center justify-between gap-4">
        <button type="button" class="text-lg" @click="current = (current - 1 + total) % total">
            &lt;
        </button>

        <div class="flex-1 text-center font-medium">
            @foreach($section->blocks as $index => $block)
                <p x-show="current === {{ $index }}">
                    {{ $block->settings->text }}
                </p>
            @endforeach
        </div>

        <button type="button" class="text-lg" @click="current = (current + 1) % total">
            &gt;
        </button>
    </div>
</section>
```

## Notes

- Use `$section->settings->key` for values.
- Use `$section->blocks` for rendering repeatable content.
- You can mix Alpine.js and Blade to match the UX requirements of your section.

---

Next: [Using Sections in Templates](./using-section.md)

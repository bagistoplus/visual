# Rendering a Section

Each section has an associated Blade view that defines how its settings and blocks are displayed. The view is automatically injected with a `$section` object.

## Accessing Data in the View

In your Blade file (e.g. `resources/views/sections/announcement-bar.blade.php`), use:

- `$section->settings` — for static fields defined in `settings()`
- `$section->children` — for accessing child blocks added by merchants
- `$section->editor_attributes` — for Visual Editor attributes (required when no wrapper is defined)

## Editor Attributes

When your section doesn't define a `wrapper` attribute, you must add <code v-pre>{{ $section->editor_attributes }}</code> to the root element of your view. This injects the necessary data attributes for the Visual Editor to identify and interact with the section.

```blade
<section {{ $section->editor_attributes }} class="announcement-bar">
    <!-- Section content -->
</section>
```

If your section defines a `wrapper` attribute, the editor attributes are automatically injected and you don't need to add them manually. See [Section Attributes - wrapper](./section-attributes.md#wrapper) for more details.

## Example: Rotating Announcement Bar

This example demonstrates an announcement bar section that accepts multiple announcement blocks.

### Section Class

```php
<?php

namespace Themes\AwesomeTheme\Sections;

use BagistoPlus\Visual\Sections\SimpleSection;
use BagistoPlus\Visual\Settings\Color;
use BagistoPlus\Visual\Support\Preset;
use BagistoPlus\Visual\Support\PresetBlock;

class AnnouncementBar extends SimpleSection
{
    protected static string $view = 'shop::sections.announcement-bar';

    public static function settings(): array
    {
        return [
            Color::make('background_color', 'Background')->default('#facc15'),
            Color::make('text_color', 'Text Color')->default('#000000'),
        ];
    }

    protected static array $accepts = ['@awesome-theme/announcement'];

    public static function presets(): array
    {
        return [
            Preset::make('Default Announcements')
                ->description('Announcement bar with three sample messages')
                ->settings([
                    'background_color' => '#facc15',
                    'text_color' => '#000000',
                ])
                ->blocks([
                    PresetBlock::make('@awesome-theme/announcement')
                        ->settings(['text' => 'Free shipping on all orders over $50']),

                    PresetBlock::make('@awesome-theme/announcement')
                        ->settings(['text' => 'Extended returns until January 31st']),

                    PresetBlock::make('@awesome-theme/announcement')
                        ->settings(['text' => 'New arrivals just added - Shop now!']),
                ]),
        ];
    }
}
```

### Section Blade View

The section view (`resources/views/sections/announcement-bar.blade.php`):

```blade
<section
    {{ $section->editor_attributes }}
    class="announcement-bar py-3 text-center font-medium"
    style="background-color: {{ $section->settings->background_color }}; color: {{ $section->settings->text_color }}"
    x-data="{
        current: 0,
        total: {{ $section->childrenCount() }},
        init() {
            if (this.total > 1) {
                setInterval(() => {
                    this.current = (this.current + 1) % this.total
                }, 5000)
            }
        }
    }"
>
    <div class="container mx-auto">
        <div class="flex items-center justify-between gap-4">
            @if($section->childrenCount() > 1)
                <button
                    type="button"
                    class="text-lg px-2"
                    @click="current = (current - 1 + total) % total"
                    aria-label="Previous announcement"
                >
                    ←
                </button>
            @endif

            <div class="flex-1 relative">
                @children
            </div>

            @if($section->childrenCount() > 1)
                <button
                    type="button"
                    class="text-lg px-2"
                    @click="current = (current + 1) % total"
                    aria-label="Next announcement"
                >
                    →
                </button>
            @endif
        </div>
    </div>
</section>
```

### Announcement Block

The announcement block that will be used inside the section. For more details on creating blocks, see [Creating a Block](../adding-blocks/creating-block.md).

**Block class** (`src/Blocks/Announcement.php`):

```php
<?php

namespace Themes\AwesomeTheme\Blocks;

use BagistoPlus\Visual\Block\SimpleBlock;
use BagistoPlus\Visual\Settings\Text;

class Announcement extends SimpleBlock
{
    protected static string $type = '@awesome-theme/announcement';
    protected static string $view = 'shop::blocks.announcement';

    public static function settings(): array
    {
        return [
            Text::make('text', 'Announcement Text')
                ->default('Your announcement message here'),
        ];
    }
}
```

**Block view** (`resources/views/blocks/announcement.blade.php`):

```blade
<div
    {{ $block->editor_attributes }}
    class="announcement-message"
    x-show="current === {{ $block->index }}"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    {{ $block->settings->text }}
</div>
```

### How it works

- The section auto-rotates announcements every 5 seconds when there are multiple messages
- Navigation arrows appear only when there's more than one announcement
- The section uses `@children` to render all announcement blocks
- Each announcement block uses `$block->index` to show/hide based on the section's `current` state
- Alpine.js transitions in the block view provide smooth fade effects

## Notes

- Use `$section->settings->key` to access section settings.
- Use `@children` to render all child blocks that merchants add through the Visual Editor.
- Always include <code v-pre>{{ $section->editor_attributes }}</code> on the root element when not using a wrapper.
- For complex interactions like carousels or tabs, consider implementing the logic in JavaScript that wraps around the rendered children.

---

Next: [Using Sections in Templates](./using-section.md)

# Defining Settings and Blocks

To make a section configurable in the Visual Editor, you need to define its settings and (optionally) repeatable blocks.

## Settings Schema

Define settings in the `settings()` method. These fields appear in the section's configuration panel in the editor.

```php
use BagistoPlus\Visual\Sections\Settings\Color;
use BagistoPlus\Visual\Sections\Settings\Link;
use BagistoPlus\Visual\Sections\Settings\Text;

public static function settings(): array
{
    return [
        Text::make('text', 'Text')->default('Free shipping on all orders'),
        Link::make('link', 'Call to Action'),
        Color::make('background_color', 'Background')->default('#facc15'),
        Color::make('text_color', 'Text Color')->default('#000000'),
    ];
}
```

→ Read more about [Settings](./../../core-concepts/settings/overview.md) and [supported setting types](../../core-concepts/settings/types.md)

## Blocks Schema

Blocks allow merchants to add repeatable content structures like slides, features, or testimonials.

Define blocks using the `Block` class:

```php
use BagistoPlus\Visual\Sections\Block;
use BagistoPlus\Visual\Sections\Settings\Text;

Block::make('announcement')
    ->settings([
        Text::make('text', 'Message')->default('Extended returns until Jan 31'),
    ])
```

### Parameters

- `type` - (string) — Internal identifier (e.g. `announcement`, `feature`)
- `name` — (optional) — Display name shown in the editor UI.

  If omitted, it defaults to the title-cased version of the type
  (testimonial → Testimonial)

### Configuration

- `limit(int)` — Max number of blocks of this type (default: 16)
- `settings(array)` — Setting definitions, just like for section settings

You can define multiple block types if needed.

## Default Data

Use the `$default` property to define the initial state of the section when added.

```php
protected static array $default = [
    'settings' => [
        'text' => 'Welcome to our store',
        'background_color' => '#facc15',
        'text_color' => '#000000',
    ],
    'blocks' => [
        ['type' => 'announcement', 'text' => 'Extended returns until Jan 31'],
        ['type' => 'announcement', 'text' => 'Free shipping on orders over $50'],
    ],
];
```

This ensures the section is immediately functional and visually populated.

---

Next: [Writing section view](./writing-section-view.md)

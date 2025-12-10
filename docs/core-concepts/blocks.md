# Blocks

Blocks are **the fundamental building units** of Bagisto Visual themes. They are **reusable, configurable components** with configurable settings that can be shared across multiple sections, enabling merchants to build custom layouts without touching code.

Each block is built as a **PHP class** paired with a **Blade view** for rendering, similar to sections but designed for granular reusability.

## Why Blocks Matter

In v1, each section's repeatable content (like buttons, testimonials, or product cards) was scoped to that section alone. A button defined in your hero section couldn't be reused in your features section. This led to duplication and inconsistency.

**v2's blocks system changes this fundamentally:**

- **For Developers**: Create blocks once, use them everywhere. Build libraries of reusable components that work across all sections.
- **For Merchants**: Compose custom layouts by mixing and matching blocks in the theme editor. Build product cards, hero sections, and entire pages from atomic building blocks.

Blocks transform Bagisto Visual from a customization tool into a **true page builder**.

## Blocks vs Sections

Understanding the distinction between blocks and sections is crucial:

| Aspect               | Blocks                                    | Sections                                    |
| -------------------- | ----------------------------------------- | ------------------------------------------- |
| **Purpose**          | Atomic, reusable components               | Containers that compose blocks into layouts |
| **Scope**            | Shared across multiple sections           | Page-level or template-level containers     |
| **Examples**         | Button, Image, Product Title, Testimonial | Hero, Product Grid, Feature List, Footer    |
| **Reusability**      | Define once, use in many sections         | Template-specific or globally available     |
| **Merchant Control** | Add, remove, arrange within sections      | Add, remove, arrange on pages               |
| **Nesting**          | Can contain other blocks (containers)     | Contain blocks                              |

**Think of it this way**: Blocks are LEGO bricks, sections are the base plates you build on.

## Anatomy of a Block

A block consists of three main parts:

- **A view**: Responsible for displaying the content, typically built with Blade templates
- **Configurable settings**: Define how merchants can customize the block's appearance and behavior
- **Schema**: Defines the block's settings, presets, and whether it can accept child blocks (for container blocks)

## Block Directory Structure

```plaintext
/theme/
├── src/Blocks/
│   ├── Button.php
│   ├── Testimonial.php
│   ├── ProductTitle.php
│   └── Columns.php          # Container block
├── resources/views/blocks/
│   ├── button.blade.php
│   ├── testimonial.blade.php
│   ├── product-title.blade.php
│   └── columns.blade.php
```

- `src/Blocks/` contains the PHP block classes
- `resources/views/blocks/` contains the corresponding Blade templates

## Basic Block Example

### PHP Block Class

```php
namespace Themes\YourTheme\Blocks;

use BagistoPlus\Visual\Block\SimpleBlock;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Link;
use BagistoPlus\Visual\Settings\Select;

class Button extends SimpleBlock
{
    protected static string $view = 'shop::blocks.button';

    public static function settings(): array
    {
        return [
            Text::make('text', 'Button text')
                ->default('Click me'),

            Link::make('url', 'Button URL'),

            Select::make('style', 'Button style')
                ->options([
                    'primary' => 'Primary',
                    'secondary' => 'Secondary',
                    'outline' => 'Outline',
                ])
                ->default('primary'),
        ];
    }
}
```

### Blade View Example

```blade
<a {{ $block->editor_attributes }} href="{{ $block->settings->url ?? '#' }}"
   class="button button--{{ $block->settings->style }}">
    {{ $block->settings->text }}
</a>
```

✅ A `$block` object is automatically injected into each Blade view. Settings can be accessed via `$block->settings`.

## Block Types

Bagisto Visual v2 provides three base block types:

### SimpleBlock

The most common block type. Basic blocks that can render HTML content directly or use Blade views. Most blocks extend `SimpleBlock`.

**Using a Blade view:**

```php
use BagistoPlus\Visual\Block\SimpleBlock;

class Button extends SimpleBlock
{
    protected static string $view = 'shop::blocks.button';

    public static function settings(): array
    {
        return [
            // Settings configuration
        ];
    }
}
```

**Rendering HTML directly:**

SimpleBlocks can also render HTML directly without a Blade view by implementing the `render()` method:

```php
use BagistoPlus\Visual\Block\SimpleBlock;

class Divider extends SimpleBlock
{
    public function render(): string
    {
        return '<hr class="divider" />';
    }
}
```

### BladeBlock

Uses Blade components instead of Blade views. Blade components allow you to leverage component features like slots, component attributes, and encapsulated logic.

```php
use BagistoPlus\Visual\Block\BladeBlock;

class Card extends BladeBlock
{
    protected static string $view = 'shop::blocks.card'; // Points to a Blade component

    public static function settings(): array
    {
        return [
            // Settings configuration
        ];
    }
}
```

The difference is that the view is treated as a Blade component rather than a simple template.

### LivewireBlock

Blocks powered by Livewire for dynamic, interactive components.

```php
use BagistoPlus\Visual\Block\LivewireBlock;

class InteractiveBlock extends LivewireBlock
{
    protected static string $component = 'shop.blocks.interactive-block';

    // Livewire component methods
}
```

## Static Blocks

Static blocks enable theme developers to have more control over the layout of their sections. They are called static blocks because they are **statically rendered** in Blade instead of dynamically rendered through the theme editor.

By default, blocks are **dynamic** - merchants can add, remove, reorder, and duplicate them in the theme editor. Static blocks, however, are fixed in place by the developer.

**Static blocks can be used in various scenarios:**

- **Bring structure to the theme** - In cases where the theme design requires blocks that should not be moved or deleted by the merchant (e.g., a hero section title that must always appear, or an icon that must stay paired with text)
- **Conditionally render blocks** - Show or hide blocks based on settings or logic (e.g., display a promotional banner only when enabled)
- **Maintain layout control** - Ensure specific blocks remain in their intended positions

**In all cases, static blocks maintain the flexibility to customize the settings.** Merchants can't move or delete static blocks, but they can still configure their appearance, content, and behavior through the settings panel.

See [Static Blocks](/building-theme/adding-blocks/static-blocks) for implementation details.

## Container Blocks (Nesting)

Some blocks can **accept child blocks**, enabling deep nesting and sophisticated layouts. These are called **container blocks**.

**Examples of container blocks:**

- **Columns**: Multi-column layouts with blocks in each column
- **Tabs**: Tabbed content with blocks in each tab
- **Accordion**: Collapsible sections with blocks inside
- **Container**: Generic wrapper for grouping blocks

Merchants can nest blocks up to 8 levels deep, creating complex layouts like:

- Columns containing tabs, each tab containing images and testimonials
- Accordions with product blocks and buttons inside
- Multi-column hero sections with nested content

See [Container Blocks](/building-theme/adding-blocks/container-blocks) for implementation details.

## Next Steps

Ready to start working with blocks? Here's your learning path:

1. **[Creating a Block](/building-theme/adding-blocks/creating-block)**: Step-by-step guide to creating your first block
2. **[Block Attributes](/building-theme/adding-blocks/block-schema)**: Configure settings and nesting
3. **[Presets](/core-concepts/presets)**: Create quick-start templates for blocks
4. **[Static Blocks](/building-theme/adding-blocks/static-blocks)**: Render blocks in Blade templates
5. **[Container Blocks](/building-theme/adding-blocks/container-blocks)**: Build blocks that accept children

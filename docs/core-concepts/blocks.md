# Blocks

Blocks are **the fundamental building units** of Bagisto Visual v2 themes. They are **reusable, configurable components** that can be shared across multiple sections, enabling merchants to build custom layouts without touching code.

Each block is built as a **PHP class** paired with a **Blade view** for rendering, similar to sections but designed for granular reusability.

## Why Blocks Matter

In v1, each section's repeatable content (like buttons, testimonials, or product cards) was scoped to that section alone. A button defined in your hero section couldn't be reused in your features section. This led to duplication and inconsistency.

**v2's blocks system changes this fundamentally:**

- **For Developers**: Create blocks once, use them everywhere. Build libraries of reusable components that work across all sections.
- **For Merchants**: Compose custom layouts by mixing and matching blocks in the theme editor. Build product cards, hero sections, and entire pages from atomic building blocks.

Blocks transform Bagisto Visual from a customization tool into a **true page builder**.

## Blocks vs Sections

Understanding the distinction between blocks and sections is crucial:

| Aspect | Blocks | Sections |
|--------|--------|----------|
| **Purpose** | Atomic, reusable components | Containers that compose blocks into layouts |
| **Scope** | Shared across multiple sections | Page-level or template-level containers |
| **Examples** | Button, Image, Product Title, Testimonial | Hero, Product Grid, Feature List, Footer |
| **Reusability** | Define once, use in many sections | Template-specific or globally available |
| **Merchant Control** | Add, remove, arrange within sections | Add, remove, arrange on pages |
| **Nesting** | Can contain other blocks (containers) | Contain blocks |

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

use BagistoPlus\Visual\Block\BladeBlock;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Link;
use BagistoPlus\Visual\Settings\Select;

class Button extends BladeBlock
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
<a href="{{ $block->settings->url ?? '#' }}"
   class="button button--{{ $block->settings->style }}">
    {{ $block->settings->text }}
</a>
```

✅ A `$block` object is automatically injected into each Blade view. Settings can be accessed via `$block->settings`.

## Block Types

Bagisto Visual v2 provides three base block types:

### BladeBlock

Standard blocks using Blade templates. Most blocks extend this class.

```php
use BagistoPlus\Visual\Block\BladeBlock;

class MyBlock extends BladeBlock
{
    protected static string $view = 'shop::blocks.my-block';

    public static function settings(): array
    {
        return [
            // Settings configuration
        ];
    }
}
```

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

### SimpleBlock

Lightweight blocks without PHP logic, defined entirely in the view.

```php
use BagistoPlus\Visual\Block\SimpleBlock;

class Divider extends SimpleBlock
{
    protected static string $view = 'shop::blocks.divider';
}
```

## Static vs Dynamic Blocks

Blocks can be used in two ways within sections:

### Dynamic Blocks

Merchants can add, remove, reorder, and duplicate these blocks in the theme editor.

**Use cases:**
- Testimonials in a testimonial section
- Slides in a carousel
- Product features in a feature list
- Menu items in navigation

### Static Blocks

Fixed blocks that cannot be reordered or deleted by merchants, but their settings can still be customized.

**Use cases:**
- Required structural elements (e.g., a title that must always appear)
- Conditional blocks (shown/hidden based on settings)
- Blocks with mandatory relationships (e.g., an icon always paired with text)

See [Static vs Dynamic Blocks](/building-theme/adding-blocks/static-vs-dynamic-blocks) for implementation details.

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

## Available Blocks in v2

While you can create custom blocks for any use case, v2 provides common block types out of the box:

**Content Blocks**: Heading, Paragraph, Rich Text, Quote, Spacer

**Media Blocks**: Image, Video, Icon, Gallery

**Interactive Blocks**: Button, Form Fields, Accordion, Tabs

**E-commerce Blocks**: Product Image, Product Title, Product Price, Product Rating, Product Labels, Product Description, Variant Selector, Add to Cart

**Layout/Container Blocks**: Columns, Tabs, Accordion, Container, Divider

**Social Proof Blocks**: Testimonial, Review, Rating, Team Member

## How Sections Use Blocks

Sections define which blocks they accept using the `accepts()` method:

```php
public static function accepts(): array
{
    return [
        'button',           // Accepts Button blocks
        'testimonial',      // Accepts Testimonial blocks
        '@theme',          // Accepts all theme blocks
    ];
}
```

Or using the `$accepts` static property:

```php
protected static array $accepts = ['button', 'testimonial', '@theme'];
```

In the section's Blade view, you render the dynamic blocks:

```blade
<div class="section-content">
    @children
</div>
```

For static blocks, use the `@visualBlock` directive (requires type and ID):

```blade
@visualBlock('button', 'section-cta')
```

Or the component syntax:

```blade
<visual:block type="button" id="section-cta" />
```

See [Using Blocks in Sections](/building-theme/adding-blocks/using-in-sections) for detailed integration.

## Block Presets

Blocks can define **presets** - pre-configured variations that merchants can quickly add from the theme editor. For example, a Button block might have "Primary CTA", "Secondary Link", and "Outline Button" presets.

```php
public static function presets(): array
{
    return [
        [
            'name' => 'Primary CTA',
            'settings' => ['text' => 'Shop Now', 'style' => 'primary', 'size' => 'large'],
        ],
        [
            'name' => 'Secondary Link',
            'settings' => ['text' => 'Learn More', 'style' => 'secondary', 'size' => 'medium'],
        ],
    ];
}
```

**[Learn more about Presets →](/core-concepts/presets)**

## Next Steps

Ready to start working with blocks? Here's your learning path:

1. **[Creating a Block](/building-theme/adding-blocks/creating-block)**: Step-by-step guide to creating your first block
2. **[Block Schema](/building-theme/adding-blocks/block-schema)**: Configure settings and nesting
3. **[Presets](/core-concepts/presets)**: Create quick-start templates for blocks
4. **[Static vs Dynamic Blocks](/building-theme/adding-blocks/static-vs-dynamic-blocks)**: Choose the right approach
5. **[Container Blocks](/building-theme/adding-blocks/container-blocks)**: Build blocks that accept children
6. **[Using in Sections](/building-theme/adding-blocks/using-in-sections)**: Integrate blocks into your sections

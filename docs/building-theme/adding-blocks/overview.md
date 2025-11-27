# Adding Blocks - Overview

Blocks are the atomic building units of Bagisto Visual v2. This guide will walk you through creating custom blocks for your theme.

## What You'll Learn

This section covers everything you need to know about building blocks:

- **[Creating a Block](/building-theme/adding-blocks/creating-block)**: Step-by-step guide to creating your first block
- **[Block Schema](/building-theme/adding-blocks/block-schema)**: Configuring settings, presets, and nested blocks
- **[Static vs Dynamic Blocks](/building-theme/adding-blocks/static-vs-dynamic-blocks)**: Understanding the two block types
- **[Rendering Blocks](/building-theme/adding-blocks/rendering-blocks)**: How to render blocks in your views
- **[Container Blocks](/building-theme/adding-blocks/container-blocks)**: Creating blocks that accept child blocks
- **[Using in Sections](/building-theme/adding-blocks/using-in-sections)**: Integrating blocks into sections

## When to Create Custom Blocks

Create custom blocks when you need:

- **Reusable components** that appear across multiple sections (buttons, testimonials, badges)
- **E-commerce elements** specific to your store (custom product cards, pricing displays)
- **Branded components** that reflect your design system (CTAs, social proof, icons)
- **Content types** that merchants will manage repeatedly (team members, features, FAQs)

## Block Types Overview

Bagisto Visual provides three base block types to extend:

### BladeBlock

The most common block type. Uses Blade templates for rendering.

**Best for:**
- Standard content blocks (text, images, buttons)
- Blocks with settings but no complex logic
- Most use cases

### LivewireBlock

Dynamic blocks powered by Livewire components.

**Best for:**
- Interactive blocks (forms, calculators, live search)
- Blocks that need real-time updates
- AJAX-based functionality

### SimpleBlock

Minimal blocks without settings or logic.

**Best for:**
- Structural elements (dividers, spacers)
- Static content
- Pure presentation blocks

## Quick Example

Here's a simple block to get you started:

**PHP Class** (`src/Blocks/Testimonial.php`):
```php
namespace Themes\YourTheme\Blocks;

use BagistoPlus\Visual\Block\BladeBlock;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Textarea;
use BagistoPlus\Visual\Settings\Image;

class Testimonial extends BladeBlock
{
    protected static string $view = 'shop::blocks.testimonial';

    public static function settings(): array
    {
        return [
            Text::make('name', 'Customer name'),
            Textarea::make('quote', 'Testimonial quote'),
            Image::make('photo', 'Customer photo'),
        ];
    }
}
```

**Blade View** (`resources/views/blocks/testimonial.blade.php`):
```blade
<div class="testimonial">
    @if($block->settings->photo)
        <img src="{{ $block->settings->photo }}" alt="{{ $block->settings->name }}">
    @endif
    <blockquote>{{ $block->settings->quote }}</blockquote>
    <cite>{{ $block->settings->name }}</cite>
</div>
```

That's it! This Testimonial block can now be used in any section that accepts it.

## Directory Structure

```plaintext
/theme/
├── src/
│   └── Blocks/
│       ├── Button.php
│       ├── Testimonial.php
│       ├── ProductCard.php
│       └── Columns.php           # Container block
├── resources/
│   └── views/
│       └── blocks/
│           ├── button.blade.php
│           ├── testimonial.blade.php
│           ├── product-card.blade.php
│           └── columns.blade.php
```

**Key points:**
- PHP classes go in `src/Blocks/`
- Blade views go in `resources/views/blocks/`
- One PHP class per block, one Blade view per block
- Class names should be PascalCase, view names should be kebab-case

## Development Workflow

1. **Plan your block**: Decide what settings it needs and where it will be used
2. **Create the PHP class**: Extend BladeBlock, LivewireBlock, or SimpleBlock
3. **Define settings**: Use the settings() method to configure merchant-editable options
4. **Create the Blade view**: Implement the block's HTML and styling
5. **Test in sections**: Add your block to sections and test in the theme editor
6. **Refine**: Iterate based on how merchants use it

## Best Practices

✅ **Keep blocks focused**: One purpose per block (button, testimonial, image)
✅ **Make them reusable**: Design for multiple contexts, not one specific section
✅ **Provide sensible defaults**: Settings should have good default values
✅ **Use clear naming**: Block names should clearly describe their purpose
✅ **Document settings**: Use descriptive labels for merchant-facing settings
✅ **Test responsiveness**: Blocks should work on all screen sizes

## Next Steps

Ready to create your first block? Start with **[Creating a Block](/building-theme/adding-blocks/creating-block)** to build a block step-by-step.

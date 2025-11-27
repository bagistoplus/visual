# Using Blocks in Sections

Sections are containers that compose blocks into cohesive layouts. This guide shows how to integrate blocks into your sections, configure which blocks are accepted, and render them effectively.

## Section-Block Relationship

Think of the relationship this way:

- **Blocks** are LEGO bricks - atomic, reusable components
- **Sections** are base plates - they define where and how blocks can be arranged

Sections don't define content; they define **structure** and **accept blocks** that provide the content.

## Accepting Blocks in Sections

Configure which blocks a section accepts using the `accepts()` method:

### Accept Specific Blocks

```php
public static function accepts(): array
{
    return [
        'testimonial',  // Accept Testimonial blocks
        'button',       // Accept Button blocks
        'image',        // Accept Image blocks
    ];
}
```

Or using the `$accepts` property:

```php
protected static array $accepts = ['testimonial', 'button', 'image'];
```

### Accept All Theme Blocks

```php
public static function accepts(): array
{
    return ['@theme'];  // Accept all theme blocks
}
```

### Mixed Approach

```php
public static function accepts(): array
{
    return [
        '@theme',          // All theme blocks
        'custom-block',    // Plus specific custom blocks
    ];
}
```

## Complete Section Example

Here's a complete testimonials section that accepts testimonial blocks:

**PHP Section Class** (`src/Sections/Testimonials.php`):

```php
<?php

namespace Themes\YourTheme\Sections;

use BagistoPlus\Visual\Section\BladeSection;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Select;
use BagistoPlus\Visual\Settings\Color;

class Testimonials extends BladeSection
{
    protected static string $view = 'shop::sections.testimonials';

    public static function settings(): array
    {
        return [
            Text::make('heading', 'Section Heading')
                ->default('What Our Customers Say'),

            Select::make('layout', 'Layout')
                ->options([
                    'grid' => 'Grid',
                    'carousel' => 'Carousel',
                    'stacked' => 'Stacked',
                ])
                ->default('grid'),

            Color::make('background_color', 'Background Color')
                ->default('#f9fafb'),
        ];
    }

    public static function accepts(): array
    {
        return ['testimonial'];  // Only accept testimonial blocks
    }
}
```

**Blade View** (`resources/views/sections/testimonials.blade.php`):

```blade
<section class="testimonials testimonials--{{ $section->settings->layout }}"
         style="background-color: {{ $section->settings->background_color }}">

    <div class="container">
        {{-- Section heading (static) --}}
        @if($section->settings->heading)
            <h2 class="testimonials-heading">
                {{ $section->settings->heading }}
            </h2>
        @endif

        {{-- Testimonial blocks (dynamic) --}}
        <div class="testimonials-grid">
            @children
        </div>
    </div>
</section>
```

## Rendering Patterns

> **Note**: Use the `@children` directive to render all dynamic blocks. The directive handles block rendering automatically.

### Simple Container

```blade
<div class="blocks-container">
    @children
</div>
```

### Grid Layout

```blade
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @children
</div>
```

For advanced layouts requiring custom wrappers per block, consult the Visual package documentation for available rendering patterns.

## Combining Static and Dynamic Blocks

Most flexible sections mix static blocks (structure) with dynamic blocks (content):

```blade
<section class="feature-section">
    {{-- Static heading --}}
    @visualBlock('heading', 'features-heading')

    {{-- Static subtitle --}}
    @if($section->settings->subtitle)
        @visualBlock('paragraph', 'features-subtitle')
    @endif

    {{-- Dynamic feature blocks --}}
    <div class="features-grid">
        @children
    </div>

    {{-- Static CTA at bottom --}}
    @if($section->settings->cta_url)
        @visualBlock('button', 'features-cta')
    @endif
</section>
```

## Handling Empty State

The `@children` directive handles empty state automatically. Blocks are only rendered if they exist:

```blade
<div class="section">
    <h2>{{ $section->settings->title }}</h2>

    {{-- Blocks are rendered here, empty state handled automatically --}}
    <div class="blocks-container">
        @children
    </div>
</div>
```

## Real-World Section Examples

### Example 1: Hero Section

Accepts heading, paragraph, image, and button blocks:

```php
public static function accepts(): array
{
    return ['heading', 'paragraph', 'image', 'button'];
}
```

```blade
<section class="hero" style="background: {{ $section->settings->background }}">
    <div class="hero-content">
        @children
    </div>
</section>
```

### Example 2: Product Grid

Accepts product card blocks:

```php
public static function accepts(): array
{
    return ['product-card'];
}
```

```blade
<section class="product-grid">
    <h2>{{ $section->settings->title }}</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        @children
    </div>
</section>
```

### Example 3: Multi-Block Section

Accepts any theme blocks:

```php
public static function accepts(): array
{
    return ['@theme'];
}
```

```blade
<section class="flexible-content">
    @children
</section>
```

**Best practices:**
- Set reasonable limits to avoid performance issues
- Consider responsive design (how many fit on mobile?)
- Match limits to your section's visual design

## Testing Section-Block Integration

Test your sections with:

1. **No blocks**: Empty state handling
2. **One block**: Layout with minimal content
3. **Many blocks**: Near or at max_blocks limit
4. **Mixed types**: If accepting @theme, test various block types
5. **Responsive**: How blocks wrap on mobile, tablet, desktop

## Best Practices

✅ **Accept appropriate blocks**: Only blocks that make sense for the section
✅ **Set max blocks**: Prevent overwhelming layouts
✅ **Handle empty state**: Show helpful message when no blocks
✅ **Provide structure**: Sections define layout, blocks provide content
✅ **Combine static/dynamic**: Use both for flexible, structured sections
✅ **Test thoroughly**: With different block counts and types
✅ **Document expectations**: Comment which blocks work best

## Next Steps

- **[Core Concepts: Sections](/core-concepts/sections)**: Understand sections in depth
- **[Rendering Blocks](/building-theme/adding-blocks/rendering-blocks)**: Master block rendering techniques
- **[Container Blocks](/building-theme/adding-blocks/container-blocks)**: Create blocks that accept children

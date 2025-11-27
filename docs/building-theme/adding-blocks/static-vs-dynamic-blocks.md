# Static vs Dynamic Blocks

Blocks can be used in two fundamentally different ways within sections: as **dynamic blocks** (merchant-controlled) or **static blocks** (developer-controlled). Understanding the distinction is crucial for building flexible, maintainable themes.

> **Note on Rendering Syntax**: Dynamic blocks use `@children` directive. Static blocks use `@visualBlock('type', 'id')` directive or `<visual:block type="..." id="..." />` component tag.

## Dynamic Blocks

Dynamic blocks can be **added, removed, reordered, and duplicated** by merchants in the theme editor.

### Characteristics

- ✅ Merchants have full control over quantity and order
- ✅ Can be duplicated
- ✅ Count toward block limits
- ✅ Appear in the "Add block" menu in the theme editor
- ❌ Cannot be conditionally rendered
- ❌ Cannot be programmatically filtered

### Use Cases

**Testimonials Section**:
Merchants add as many testimonial blocks as needed, reorder them, or remove some.

**Image Carousel**:
Merchants control the number of slides and their order.

**Feature List**:
Merchants add feature blocks, each highlighting a different product benefit.

**Product Grid**:
Merchants choose which product blocks to display in the grid.

### Implementation

Dynamic blocks are defined in the section's `accepts()` method:

```php
public static function accepts(): array
{
    return [
        'testimonial',  // Merchants can add Testimonial blocks
        'feature',      // Merchants can add Feature blocks
    ];
}
```

### Rendering Dynamic Blocks

In the section view, use the `@children` directive:

```blade
<div class="testimonials">
    @children
</div>
```

## Static Blocks

Static blocks are **fixed in the section** and cannot be reordered or deleted by merchants. However, their **settings** can still be customized.

### Characteristics

- ✅ Developer controls structure and presence
- ✅ Can be conditionally rendered
- ✅ Settings are still customizable by merchants
- ✅ Don't count toward block limits
- ❌ Cannot be reordered by merchants
- ❌ Cannot be duplicated or deleted
- ❌ Don't appear in "Add block" menu

### Use Cases

**Required Header Elements**:
A logo that must always appear, but merchants can upload their own image.

**Conditional Content**:
A promotion banner that only shows if a setting is enabled.

**Mandatory Relationships**:
An icon that must always accompany a title in a specific layout.

**Structural Components**:
A container that must be present, but its content is customizable.

### Implementation

Static blocks are defined directly in the section view, not in the schema:

```blade
<div class="hero-section">
    {{-- Static block: Always present, conditionally rendered --}}
    @if($section->settings->show_badge)
        @visualBlock('badge', 'promo-badge')
    @endif

    {{-- Static heading block: Always present --}}
    @visualBlock('heading', 'hero-title')

    {{-- Dynamic blocks: Merchants control --}}
    @children
</div>
```

### The `@visualBlock` Directive

Create static block instances using the directive (requires type and unique ID):

```blade
@visualBlock('type', 'unique-id')
```

**Examples:**

```blade
{{-- Simple static block --}}
@visualBlock('button', 'hero-cta')

{{-- Conditional static block --}}
@if($section->settings->show_badge)
    @visualBlock('badge', 'sale-badge')
@endif
```

### Component Syntax

Or use the component tag with additional attributes:

```blade
{{-- Basic usage --}}
<visual:block type="button" id="hero-cta" />

{{-- With additional attributes (available in block view context) --}}
<visual:block
    type="button"
    id="hero-cta"
    text="Shop Now"
    url="/shop"
/>
```

> **Important**: The `id` parameter uniquely identifies the static block instance. Additional attributes are passed to the block view context but don't override block settings defined in PHP or the theme editor.

## Comparison Table

| Feature | Dynamic Blocks | Static Blocks |
|---------|----------------|---------------|
| **Add/Remove** | ✅ Merchants control | ❌ Fixed by developer |
| **Reorder** | ✅ Merchants control | ❌ Fixed order |
| **Duplicate** | ✅ Yes | ❌ No |
| **Settings Editable** | ✅ Yes | ✅ Yes |
| **Conditional Rendering** | ❌ No | ✅ Yes |
| **Count Toward Limit** | ✅ Yes | ❌ No |
| **In "Add Block" Menu** | ✅ Yes | ❌ No |
| **Best For** | Merchant-controlled content | Required structural elements |

## Real-World Examples

### Example 1: Product Features Section

**Scenario**: A section showcasing product features with a required heading and optional feature list.

```php
// Section class
public static function settings(): array
{
    return [
        Text::make('title', 'Section Title')
            ->default('Why Choose Us'),

        Checkbox::make('show_subtitle', 'Show Subtitle')
            ->default(true),

        Text::make('subtitle', 'Subtitle')
            ->default('The best choice for your business'),
    ];
}

public static function accepts(): array
{
    return ['feature'];  // Dynamic: merchants add features
}
```

```blade
{{-- Section view --}}
<div class="features-section">
    {{-- Static heading: Always present --}}
    @visualBlock('heading', 'features-title')

    {{-- Static subtitle: Conditionally rendered --}}
    @if($section->settings->show_subtitle)
        @visualBlock('paragraph', 'features-subtitle')
    @endif

    {{-- Dynamic features: Merchant-controlled --}}
    <div class="features-grid">
        @children
    </div>
</div>
```

### Example 2: Hero Banner with Required CTA

**Scenario**: Hero section with mandatory heading/description, plus 1-2 optional CTA buttons.

```php
public static function settings(): array
{
    return [
        Text::make('heading', 'Heading')
            ->required(),

        Textarea::make('description', 'Description'),

        Image::make('background', 'Background Image'),
    ];
}

public static function accepts(): array
{
    return ['button'];  // Dynamic CTAs
}
```

```blade
<div class="hero" style="background-image: url({{ $section->settings->background }})">
    <div class="hero-content">
        {{-- Static heading: Always present --}}
        @visualBlock('heading', 'hero-heading')

        {{-- Static description: Always present --}}
        @visualBlock('paragraph', 'hero-description')

        {{-- Dynamic CTAs: Merchants add 1-2 buttons --}}
        <div class="hero-actions">
            @children
        </div>
    </div>
</div>
```

## When to Use Each Type

### Use Dynamic Blocks When:

- ✅ Merchants need to control quantity (testimonials, features, slides)
- ✅ Order matters and merchants should control it (carousel, timeline)
- ✅ Content varies greatly between stores (product highlights, team members)
- ✅ Merchants need to experiment with different combinations

### Use Static Blocks When:

- ✅ Structure is mandatory (required headings, footer elements)
- ✅ Conditional logic is needed (show/hide based on settings)
- ✅ Relationships must be preserved (icon + title always together)
- ✅ You want to provide pre-built layouts with customizable content

## Mixing Both Approaches

The most flexible sections combine static and dynamic blocks:

```blade
<div class="section">
    {{-- Static: Required title --}}
    @visualBlock('heading', 'section-title')

    {{-- Dynamic: Merchant-controlled content blocks --}}
    @children

    {{-- Static: Required footer CTA --}}
    @visualBlock('button', 'section-cta')
</div>
```

This gives merchants flexibility where needed while maintaining essential structure.

## Best Practices

✅ **Default to dynamic**: Give merchants control unless there's a good reason not to
✅ **Use static for structure**: Mandatory elements should be static
✅ **Combine both**: Most sections benefit from mixing approaches
✅ **Document intent**: Comment why blocks are static vs dynamic
✅ **Provide context**: Use help text to explain limitations
✅ **Test both paths**: Ensure static conditionals work correctly

## Next Steps

- **[Rendering Blocks](/building-theme/adding-blocks/rendering-blocks)**: Learn how to render blocks in your views
- **[Container Blocks](/building-theme/adding-blocks/container-blocks)**: Create blocks that accept child blocks
- **[Using in Sections](/building-theme/adding-blocks/using-in-sections)**: Integrate blocks into your sections

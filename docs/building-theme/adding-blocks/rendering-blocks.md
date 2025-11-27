# Rendering Blocks

This guide covers how to render blocks in your Blade views, access block data, handle nesting, and implement advanced rendering techniques.

## Rendering Methods

### Dynamic Blocks in Sections

Use `@children` to render all dynamic blocks added by merchants:

```blade
{{-- Section view --}}
<div class="section-content">
    @children
</div>
```

### Static Blocks

Use `@visualBlock` directive to render static blocks (requires type and unique ID):

```blade
@visualBlock('button', 'hero-cta')
```

Or use the component syntax:

```blade
<visual:block type="button" id="hero-cta" />
```

You can pass additional attributes that become available in the block view:

```blade
<visual:block type="button" id="hero-cta" text="Shop Now" url="/shop" />
```

> **Note**: Block settings are defined in the block's PHP class via the `settings()` method or configured in the theme editor. The `id` parameter is used to uniquely identify the static block instance.

## Basic Block Rendering

### The $block Object

Every block view receives a `$block` object with the following properties:

```blade
{{-- Access block settings --}}
{{ $block->settings->title }}
{{ $block->settings->color }}

{{-- Block metadata --}}
{{ $block->id }}           {{-- Unique identifier --}}
{{ $block->type }}         {{-- Block type name --}}

{{-- Child blocks (for container blocks) --}}
@children
```

### Rendering Block Settings

```blade
{{-- Text settings --}}
<h2>{{ $block->settings->heading }}</h2>
<p>{{ $block->settings->description }}</p>

{{-- Image settings --}}
@if($block->settings->image)
    <img src="{{ $block->settings->image }}" alt="{{ $block->settings->alt_text }}">
@endif

{{-- Link settings --}}
<a href="{{ $block->settings->url ?? '#' }}">
    {{ $block->settings->link_text }}
</a>

{{-- Color settings --}}
<div style="background-color: {{ $block->settings->background_color }}">
    Content
</div>

{{-- Select/Radio settings --}}
<div class="block--{{ $block->settings->style }}">
    Styled content
</div>
```

## Rendering Dynamic Blocks in Sections

In section views, use `@children` to render all dynamic blocks:

```blade
{{-- Simple rendering --}}
<div class="blocks">
    @children
</div>

{{-- With wrapper class --}}
<div class="grid">
    @children
</div>
```

The `@children` directive automatically renders all blocks that merchants have added to the section through the theme editor.

## Rendering Static Blocks

Create and render static blocks using the `@visualBlock` directive:

```blade
{{-- Simple static block --}}
@visualBlock('button', 'cta-button')

{{-- Another static block --}}
@visualBlock('heading', 'section-title')

{{-- Conditional static block --}}
@if($section->settings->show_badge)
    @visualBlock('badge', 'promo-badge')
@endif
```

Or use the component syntax with additional attributes:

```blade
{{-- Pass attributes to block view context --}}
<visual:block type="button" id="cta-button" text="Click Me" url="/shop" />

{{-- Attributes from section settings --}}
<visual:block
    type="heading"
    id="section-title"
    text="{{ $section->settings->title }}"
    level="2"
/>
```

> **Note**: Additional attributes (like `text`, `url`, `level`) are passed to the block view and available in the view context, but they don't override the block's settings configured in PHP or the theme editor.

## Rendering Child Blocks (Nesting)

For container blocks that accept children, use `@children` inside the container block view:

```blade
{{-- Container block view (e.g., Columns) --}}
<div class="columns columns--{{ $block->settings->column_count }}">
    @children
</div>
```

### Nested Block Example

```blade
{{-- Tabs container block --}}
<div class="tabs">
    <div class="tabs-nav">
        @foreach($block->blocks as $index => $tab)
            <button class="tab-button" data-tab="{{ $index }}">
                {{ $tab->settings->title ?? 'Tab ' . ($index + 1) }}
            </button>
        @endforeach
    </div>

    <div class="tabs-content">
        @foreach($block->blocks as $index => $tab)
            <div class="tab-pane" data-tab="{{ $index }}">
                {!! $tab->render() !!}
            </div>
        @endforeach
    </div>
</div>
```

## Advanced Rendering Techniques

> **Note**: The `@children` directive renders all blocks automatically. For advanced control over block rendering (filtering, grouping, custom wrappers), consult the Visual package documentation for available slot/component patterns.

## Block View Best Practices

### Use Semantic HTML

```blade
{{-- Good: Semantic structure --}}
<article class="testimonial">
    <blockquote>{{ $block->settings->quote }}</blockquote>
    <cite>{{ $block->settings->author }}</cite>
</article>

{{-- Avoid: Generic divs --}}
<div class="testimonial">
    <div>{{ $block->settings->quote }}</div>
    <div>{{ $block->settings->author }}</div>
</div>
```

### Handle Missing Data Gracefully

```blade
{{-- Check before rendering --}}
@if($block->settings->image)
    <img src="{{ $block->settings->image }}"
         alt="{{ $block->settings->alt_text ?? '' }}">
@endif

{{-- Provide fallbacks --}}
<h2>{{ $block->settings->heading ?? 'Untitled' }}</h2>

{{-- Use null coalescing --}}
<a href="{{ $block->settings->url ?? '#' }}">
    {{ $block->settings->text ?? 'Read More' }}
</a>
```

### Escape Output

```blade
{{-- Escape user input --}}
<p>{{ $block->settings->description }}</p>

{{-- Raw output only for trusted HTML --}}
<div>{!! $block->settings->rich_text !!}</div>

{{-- Attributes --}}
<div class="{{ $block->settings->css_class }}">
    Content
</div>
```

### Responsive Images

```blade
@if($block->settings->image)
    <picture>
        <source srcset="{{ $block->settings->image }}" media="(min-width: 768px)">
        <img src="{{ $block->settings->mobile_image ?? $block->settings->image }}"
             alt="{{ $block->settings->alt_text }}"
             loading="lazy">
    </picture>
@endif
```

## Complete Example: Card Block

Here's a complete example showing all rendering techniques:

**Block View** (`resources/views/blocks/card.blade.php`):

```blade
<div class="card card--{{ $block->settings->style }}"
     @if($block->settings->background_color)
     style="background-color: {{ $block->settings->background_color }}"
     @endif>

    @if($block->settings->image)
        <div class="card-image">
            <img src="{{ $block->settings->image }}"
                 alt="{{ $block->settings->image_alt ?? '' }}"
                 loading="lazy">
        </div>
    @endif

    <div class="card-content">
        @if($block->settings->badge)
            <span class="card-badge">{{ $block->settings->badge }}</span>
        @endif

        <h3 class="card-title">
            {{ $block->settings->title }}
        </h3>

        @if($block->settings->description)
            <p class="card-description">
                {{ $block->settings->description }}
            </p>
        @endif

        {{-- Render child blocks (if this is a container) --}}
        <div class="card-actions">
            @children
        </div>

        {{-- Or static CTA --}}
        @if($block->settings->cta_url)
            <a href="{{ $block->settings->cta_url }}" class="card-cta">
                {{ $block->settings->cta_text ?? 'Learn More' }}
            </a>
        @endif
    </div>
</div>
```

**Section Using Card Blocks** (`resources/views/sections/features.blade.php`):

```blade
<section class="features">
    {{-- Static heading --}}
    @visualBlock('heading', 'features-title')

    {{-- Dynamic card blocks --}}
    <div class="features-grid">
        @children
    </div>
</section>
```

## Debugging Block Rendering

### Dump Block Data

```blade
{{-- See all block data --}}
@dump($block)

{{-- See block settings --}}
@dump($block->settings)

{{-- See specific setting --}}
@dump($block->settings->title)
```

### Check Block Type

```blade
{{-- Debug block type --}}
<div data-debug="Block type: {{ $block->type }}">
    {!! $block->render() !!}
</div>
```

## Next Steps

- **[Container Blocks](/building-theme/adding-blocks/container-blocks)**: Deep dive into blocks that accept children
- **[Using in Sections](/building-theme/adding-blocks/using-in-sections)**: Complete guide to section-block integration
- **[Block Schema](/building-theme/adding-blocks/block-schema)**: Configure block settings and presets

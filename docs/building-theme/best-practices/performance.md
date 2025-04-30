# Performance

A fast storefront isn't just a nice-to-have — it drives conversions, improves SEO, reduces bounce, and creates a smoother experience for merchants and customers alike.

This guide provides practical tips for building performant Bagisto Visual themes and sections, with a focus on the **live storefront**.

> 🧩 Editor preview performance is covered separately: [Integrating with the Editor](../adding-sections/integrating-editor.md)

## 1. Keep Your Markup Lean

- Don’t over-nest elements. Avoid structures like `<div><div><div>...</div></div></div>`
- Use semantic elements (`<section>`, `<h2>`, `<ul>`) to reduce overhead and improve clarity
- Only render content when needed:

```blade
@if ($section->settings->heading)
  <h2 class="text-primary">{{ $section->settings->heading }}</h2>
@endif
```

- Use `@empty`, `@unless`, or `??` to prevent rendering empty elements

## 2. Load Images Responsibly

- Add `loading="lazy"` to all images that aren’t visible on first render
- Always define `width` and `height` to prevent layout shifts
- Use compressed and appropriately sized images
- For responsive assets, use `srcset` and `sizes`

```blade
<img
  src="{{ $section->settings->image }}"
  alt="{{ $section->settings->alt }}"
  width="800"
  height="400"
  loading="lazy"
/>
```

## 3. Defer Section-Specific JavaScript

- Use `@push('scripts')` or similar to load JS **only when the section is used**
- Avoid global scripts for features like carousels or modals that are section-scoped
- Add `type="module"` and `defer` to reduce blocking

```blade
@push('scripts')
  <script type="module" defer>
    // Behavior tied to this section
  </script>
@endpush
```

## 4. Avoid Expensive Blade Logic

- Avoid database queries, `json_decode`, or filtering inside Blade
- Do data prep in the section class (controller layer)
- Use Laravel collections sparingly inside views — prefer `collect($data)->filter()` in PHP

✅ Good:

```php
return [
    'products' => Product::limit(4)->get(),
];
```

🚫 Avoid:

```blade
@foreach (Product::all() as $product)
```

## 5. Prioritize Mobile Responsiveness

- Mobile users are the majority — test every section at 320px, 375px, and 768px
- Avoid fixed-width images and layouts
- Use readable text sizes and adequate spacing
- Ensure buttons are at least 44×44px tappable

## 6. Test Like a Real User

Use these tools to simulate real-world performance:

- [PageSpeed Insights](https://pagespeed.web.dev/)
- [WebPageTest](https://www.webpagetest.org/)
- Lighthouse (Chrome DevTools → Audits tab)

Simulate:

- Slow 3G
- Low CPU
- Cold cache

And fix:

- Layout shifts
- Image pop-ins
- Unused code

## Summary

- Write efficient markup and only render what's necessary
- Lazy-load images with proper dimensions
- Keep JS scoped and defer it when possible
- Avoid expensive logic in Blade — prep data in PHP
- Prioritize mobile users and test your work with real tools

Fast themes feel better, rank better, and convert better.

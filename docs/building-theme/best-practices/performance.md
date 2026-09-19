# Performance

A fast storefront isn't just a nice-to-have — it drives conversions, improves SEO, reduces bounce, and creates a smoother experience for merchants and customers alike.

This guide provides practical tips for building performant Bagisto Visual themes and sections, with a focus on the **live storefront**.

> 🧩 Editor preview performance is covered separately: [Integrating with the Editor](./integrating-editor.md)

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
  alt="{{ $section->settings->image->alt }}"
  style="object-position: {{ $section->settings->image->objectPosition() }}"
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

## 7. Keep Personal Markup Out of the Cached Page

Bagisto's full page cache stores a rendered page and replays it. It keys the page
on channel, locale, currency and the customer id, so **every guest shares one
entry**, and a signed in customer keeps their own copy for the whole cache
lifetime. The session cart is not part of the key at all.

So anything you render that differs between two visitors is either handed to the
wrong guest, or frozen for the right one. A cart badge, a wishlist heart, a
"Welcome, Sarah", a flash message, a signed in flag inside an `x-data`.

Wrap it in a FPC region. Visual empties the region on the copy the cache stores, and
rebuilds it for whoever that copy is later served to.

```blade
@fpcRegion('cart-count')
  <span class="badge">{{ $count }}</span>
@endFpcRegion
```

Register the matching resolver in your theme's service provider. It runs for the
visitor being served, which on a cache hit is a request that renders no page at
all, so reach for what you need rather than expecting it to be passed in:

```php
use BagistoPlus\Visual\Facades\Visual;

Visual::registerFpcRegion('cart-count', fn () => view('shop::partials.cart-badge', [
    'count' => cart()->getCart()?->items_count ?? 0,
])->render());
```

Three things to know:

- **A FPC region is delimited by HTML comments**, so it can wrap nothing at all,
  which is what a count of zero renders. It cannot reach inside an attribute
  value, so keep personal values out of `x-data` and read them back from the DOM.
- **Forget the resolver and the region comes back empty.** The name in the view
  and the name in the resolver are the whole contract.
- **For a region a page needs once** however many blocks ask for it, use
  `@fpcRegionOnce`. A later caller does not run the body at all, so an expensive
  payload is built once rather than once per block:

  ```blade
  @fpcRegionOnce('shopper-state')
    <script type="application/json">{!! $json !!}</script>
  @endFpcRegionOnce
  ```

  Blade's own `@once` will not do it, because the view factory forgets what it
  has rendered once each time the render stack empties, and a block is its own
  top level render.

This only helps where PHP runs. Behind Varnish, a CDN or an nginx microcache the
request never reaches Laravel and the personal parts are wrong again.

## Summary

- Write efficient markup and only render what's necessary
- Lazy-load images with proper dimensions
- Keep JS scoped and defer it when possible
- Avoid expensive logic in Blade — prep data in PHP
- Prioritize mobile users and test your work with real tools
- Wrap anything visitor-specific in a cache region

Fast themes feel better, rank better, and convert better.

# Accessibility

Bagisto Visual is designed to help merchants create beautiful storefronts — but beauty should be accessible to everyone. As a theme or section developer, it’s your responsibility to ensure that the interfaces you build can be used by all visitors, including those with disabilities.

This guide outlines practical ways to make your themes and sections accessible by default.

## Why Accessibility Matters

- It ensures your store is usable by people with visual, motor, cognitive, and hearing impairments
- It improves SEO, performance, and mobile usability
- It's required by law in many countries
- It's good design

> Accessibility is not an optional layer — it should be part of every design decision.

## 1. Use Semantic HTML

Write markup that reflects the structure and purpose of content.

| Instead of...             | Use...                         |
| ------------------------- | ------------------------------ |
| `<div class="title">`     | `<h2>`                         |
| `<span class="link">`     | `<a href="#">`                 |
| `<div class="list-item">` | `<li>` inside `<ul>` or `<ol>` |

### Common Roles

- Use `<header>`, `<main>`, `<footer>`, `<nav>`, `<section>`, and `<article>` where appropriate
- Group content with proper heading levels (`<h1>` to `<h6>`) in logical order

## 2. Ensure Sufficient Color Contrast

When defining a color scheme, always test that text is readable over its background.

- Use tokens like `text-on-surface` and `text-on-primary` to guarantee contrast
- Test color contrast with tools like [WebAIM’s Contrast Checker](https://webaim.org/resources/contrastchecker/)
- Ensure headings, body text, buttons, and alerts pass WCAG AA contrast thresholds

## 3. Support Keyboard Navigation

Ensure that interactive elements can be used without a mouse:

- All buttons, links, and form controls must be focusable (`tabindex="0"` if needed)
- Use visible focus states (e.g. outlines or color changes)
- Avoid requiring hover-only actions

> Never hide essential functionality behind `:hover` or JS without a keyboard fallback.

## 4. Use Meaningful `alt` Text for Images

If your section includes an image setting:

```php
Image::make('image', 'Hero Image')
```

You should provide an optional `alt` field:

```php
Text::make('image_alt', 'Image alt text')
```

Then output it in your Blade file:

```blade
<img src="{{ $section->settings->image }}" alt="{{ $section->settings->alt }}">
```

If the image is decorative, use `alt=""` and set `aria-hidden="true"`.

## 5. Label Form Controls

Any inputs (search bars, newsletter forms, etc.) must have accessible labels.

### Recommended

```blade
<label for="email" class="block text-neutral">Your Email</label>
<input id="email" type="email" name="email" class="bg-surface text-on-surface" />
```

Avoid using placeholders as a replacement for labels.

## 6. Don’t Rely on Color Alone

Color should enhance meaning — not carry it entirely.

### ⚠️ Bad

> Red text = error
> Green text = success

### ✅ Better

Include icons, labels, or text to indicate purpose:

```blade
<div class="bg-error text-on-error flex items-center gap-2">
  <svg ... aria-hidden="true" />
  <span>Error: Please enter your email address</span>
</div>
```

## 7. Use ARIA Sparingly and Correctly

Only use ARIA roles and attributes when native HTML can’t do the job.

- Use `aria-label`, `aria-describedby`, or `aria-hidden` when needed
- Avoid misuse like `role="button"` on a `<div>` — use `<button>` instead
- If you're building a custom dropdown or tab system, research proper ARIA patterns first

## 8. Accessibility Testing

You don’t have to guess — there are tools that can help you check how accessible your section or theme really is.

### Recommended tools

- [Accessibility Insights for Web](https://accessibilityinsights.io/)
- [Lighthouse (Chrome DevTools)](https://developer.chrome.com/docs/lighthouse/accessibility/)
- [WAVE Evaluation Tool](https://wave.webaim.org/)

These tools can help you:

- Detect missing labels or alt attributes
- Test contrast between foreground and background
- Identify focus order or keyboard traps
- Catch improper heading structure

## Summary

- Use semantic, accessible markup — not just styled `<div>`s
- Ensure contrast, focusability, and proper labels
- Use `alt` text and avoid relying on color alone
- Test your sections with real assistive tools

Design for everyone. Accessible sections create better experiences — for every shopper.

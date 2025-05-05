# Styling and the Color System

To ensure consistent design and easy customization across themes and third-party sections, **Bagisto Visual promotes a shared, semantic color system**.

This system is implemented in the default `visual-debut` theme and is strongly recommended for all themes and section packages.

Inspired by the [DaisyUI color system](https://daisyui.com/docs/colors/), this approach uses role-based tokens to ensure legibility, adaptability, and design consistency.

## 1. Color Roles

Each color has a **semantic role** that defines its purpose in the interface. These roles provide a stable design language that works across themes and sections.

| Role             | Description                               | Common Use                           |
| ---------------- | ----------------------------------------- | ------------------------------------ |
| `primary`        | Main brand color                          | Buttons, links, CTAs                 |
| `on-primary`     | Foreground on `primary`                   | Text/icons on primary backgrounds    |
| `secondary`      | Supporting tone                           | Headings, badges, accents            |
| `on-secondary`   | Foreground on `secondary`                 | Text/icons on secondary backgrounds  |
| `accent`         | Decorative or promotional highlights      | Banners, badges                      |
| `on-accent`      | Foreground on `accent`                    | Text/icons on accent elements        |
| `neutral`        | General-purpose text and passive surfaces | Body text, input borders             |
| `on-neutral`     | Foreground on `neutral`                   | Text/icons on neutral surfaces       |
| `background`     | Page background                           | Layout wrappers, full-width sections |
| `on-background`  | Foreground on `background`                | Body text, links                     |
| `surface`        | Component or section background           | Cards, forms, sidebars               |
| `on-surface`     | Foreground on `surface`                   | Section text, icons                  |
| `surface-alt`    | Alternate surface (hover, nesting layer)  | Hover states, dropdowns              |
| `on-surface-alt` | Foreground on `surface-alt`               | Icons or overlays                    |
| `success`        | Positive feedback                         | Alerts, tags, confirmations          |
| `on-success`     | Foreground on `success`                   | Text/icons on success backgrounds    |
| `warning`        | Attention or caution                      | Notices, validation warnings         |
| `on-warning`     | Foreground on `warning`                   | Text/icons on warning backgrounds    |
| `danger`         | Critical or destructive state             | Errors, danger zones                 |
| `on-danger`      | Foreground on `error`                     | Text/icons on error messages         |
| `info`           | Informational messages                    | Tooltips, banners, guidance          |
| `on-info`        | Foreground on `info`                      | Text/icons on info sections          |

Each background is paired with a `on-*` foreground token for text and icon contrast.

## 2. Color Schemes

A **color scheme** is a named set of color tokens that defines the look and feel of a section or page.
Color schemes let themes support multiple visual styles (light, dark, promotional), and allow merchants to assign schemes without writing code.

### How They Work

- Color schemes are defined by the theme using the [ColorSchemeGroup](../../core-concepts/settings/types.md#colorschemegroup) setting type
- Each scheme includes:
  - A unique **key** (e.g. `default`, `dark`, `highlight`)
  - A **label** for display in the Visual Editor
  - A complete set of semantic tokens (see list above)
- The `default` (or first) scheme is applied to the entire page
- Sections can override the global scheme using the [ColorScheme](../../core-concepts/settings/types.md#colorscheme) setting
- Selected schemes are applied using a `data-color-scheme` attribute, which scopes CSS variables

### Important

> 💡 **Every scheme must define a value for all color roles documented above.**
> This ensures that any section relying on semantic tokens can render correctly and remain compatible.

---

### Why They Matter

- **Consistent branding** across all pages and sections
- **Flexible design** with support for dark mode, promotional themes, etc.
- **Third-party compatibility** without visual clashes
- **Merchant control** over appearance via the Visual Editor

---

### Best Practices

- Define **every required token** (`primary`, `on-primary`, `background`, etc.)
- Ensure strong contrast between background and `on-*` foregrounds
- Offer 2–5 thoughtful schemes with clear visual identity
- Render all scheme tokens in your layout using:
  ```blade
  @foreach ($theme->settings->color_schemes as $scheme)
    [data-color-scheme="{{ $scheme->id }}"] {
      {!! $scheme->outputCssVars() !!}
    }
  @endforeach
  ```

## 3. Usage Guidelines

Use role-based classes and tokens instead of hardcoded colors.

### ✅ Recommended

```blade
<div class="bg-surface text-on-surface p-6 rounded">
  <h2 class="text-secondary">Section Title</h2>
  <p class="text-neutral">Section body content goes here.</p>
</div>
```

### 🚫 Avoid

```blade
<div style="background-color: #f3f4f6; color: #333;">
  <p>This will break compatibility with color schemes.</p>
</div>
```

## 4. Theme Developer Responsibilities

Theme developers must:

- Define all required tokens per scheme using CSS variables:
  ```css
  --color-primary, --color-on-primary,
  --color-surface, --color-on-surface, etc.
  ```
- Use **kebab-case** naming for consistency
- Ensure visual contrast between paired tokens (e.g., `on-background` and `background`)
- Store the token definitions inside a single `ColorSchemeGroup` setting

## 5. Section Developer Responsibilities

Section developers should:

- Use **only semantic tokens** (`bg-surface`, `text-on-primary`, etc.)
- **Never** use hardcoded hex values or shortcuts like `text-white`
- Always pair foreground text with its appropriate background token
- Avoid relying on color alone to communicate meaning — use icons, labels, or layout cues
- Make sections compatible with any theme or scheme

## 6. Common Patterns

### Buttons

```blade
<button class="bg-primary text-on-primary px-4 py-2 rounded">
  Add to Cart
</button>
```

### Alerts

```blade
<div class="bg-warning text-on-warning p-4 rounded">
  Please check your shipping details.
</div>
```

### Cards

```blade
<div class="bg-surface text-on-surface p-6 rounded shadow">
  <h3 class="text-secondary">Your Benefits</h3>
  <ul class="text-neutral list-disc ml-4">
    <li>Free shipping</li>
    <li>Easy returns</li>
  </ul>
</div>
```

## Summary

- Use **role-based tokens** like `primary`, `surface`, `error`, etc.
- Always include `on-*` counterparts for text and icon contrast
- Define color schemes centrally using `ColorSchemeGroup`
- Let sections reference schemes using `ColorScheme`
- Avoid fixed colors — rely on design tokens and scoping for flexibility

This color system makes your sections consistent, theme-compatible, and easy to adapt for any brand or layout.

# Using Sections in Templates

Once a section is defined, it can be used in a page template a layout.

## Usage in JSON Templates

:::: tabs

::: tab JSON

```json
{
  "sections": {
    "announcement-bar": {
      "type": "awesome-theme::announcement-bar",
      "settings": {
        "text": "Free shipping on all orders",
        "background_color": "#facc15",
        "text_color": "#000000"
      },
      "blocks": {
        "first": {
          "type": "announcement",
          "settings": {
            "text": "Extended returns until Jan 31"
          }
        },
        "second": {
          "type": "announcement",
          "settings": {
            "text": "Free delivery on orders over $50"
          }
        }
      }
    }
  },
  "order": ["announcement-bar"]
}
```

:::

::: tab YAML

```yaml
sections:
  announcement-bar:
    type: awesome-theme::announcement-bar
    settings:
      text: Free shipping on all orders
      background_color: '#facc15'
      text_color: '#000000'
    blocks:
      first:
        type: announcement
        settings:
          text: Extended returns until Jan 31
      second:
        type: announcement
        settings:
          text: Free delivery on orders over $50

order:
  - announcement-bar
```

:::
::::

### Fields

| Field      | Description                                                       |
| ---------- | ----------------------------------------------------------------- |
| `sections` | Map of section instances keyed by a unique name                   |
| `order`    | List of section keys (from `sections`) to control rendering order |
|            |                                                                   |
| `type`     | Section slug (use a vendor prefix for package-defined ones)       |
| `settings` | Section-level settings                                            |
| `blocks`   | Named blocks, each with a `type` and `settings`                   |

---

### Referencing a Section

Each section in the template is referenced using its `slug`:

```php
protected static string $slug = 'announcement-bar';
```

If the section belongs to a theme package, you should use a namespace prefix:

```yaml
type: awesome-theme::announcement-bar
```

This ensures the editor resolves the correct section, even when multiple packages define similar slugs.

## Usage in Blade views

In addition to JSON templates, sections can also be embedded **statically** in Blade views using the `<visual:section>` component.

This renders the section in a fixed location on the page.
These statically-included sections:

- **Cannot be reordered or removed** via the Visual Editor
- **Can still have their settings and blocks edited**
- Are rendered at a fixed location in the layout

### Syntax

```blade
<visual:section name="section-slug" />
```

For theme-based sections, use a vendor-prefixed slug:

```blade
<visual:section name="awesome-theme::footer" />
```

---

### Example: Static Header and Footer in Layout

In your `default.blade.php` layout

```blade
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>Awesome Theme</title>
    @bagisto_vite(['resources/assets/css/app.css', 'resources/assets/js/app.js'], 'awesome-theme')
</head>
<body>

    <visual:section name="awesome-theme::header" />

    <main>
        @visual_layout_content
    </main>

    <visual:section name="awesome-theme::footer" />

</body>
</html>
```

---

### Notes

- A section can only be used statically once per page (layout + template combined)
- This is ideal for layout-bound sections like headers, footers, sidebars, banners

# Using Sections in Templates

Once a section is defined, it can be used in a page template a layout.

## Usage in JSON Templates

Sections can be used in JSON or YAML template files. See [JSON/YAML Templates](../../core-concepts/templates/json-yaml.md) for more details.

:::: tabs

::: tab JSON

```json
{
  "sections": {
    "announcement-bar": {
      "type": "@awesome-theme/announcement-bar",
      "settings": {
        "text": "Free shipping on all orders",
        "background_color": "#facc15",
        "text_color": "#000000"
      },
      "blocks": {
        "first": {
          "type": "@awesome-theme/announcement",
          "settings": {
            "text": "Extended returns until Jan 31"
          }
        },
        "second": {
          "type": "@awesome-theme/announcement",
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
    type: '@awesome-theme/announcement-bar'
    settings:
      text: Free shipping on all orders
      background_color: '#facc15'
      text_color: '#000000'
    blocks:
      first:
        type: '@awesome-theme/announcement'
        settings:
          text: Extended returns until Jan 31
      second:
        type: '@awesome-theme/announcement'
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
| `type`     | Section type (e.g., `@awesome-theme/announcement-bar`)            |
| `settings` | Section-level settings                                            |
| `blocks`   | Named blocks, each with a `type` and `settings`                   |

## Usage in PHP Templates

You can also use sections programmatically using the `TemplateBuilder` API in PHP templates. See [PHP Templates](../../core-concepts/templates/php-templates.md) for more details.

```php
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;

return TemplateBuilder::make()
    ->section('announcement-bar', '@awesome-theme/announcement-bar', fn($section) => $section
        ->settings([
            'text' => 'Free shipping on all orders',
            'background_color' => '#facc15',
            'text_color' => '#000000',
        ])
        ->blocks([
            $section->block('first', '@awesome-theme/announcement', fn($block) => $block
                ->settings(['text' => 'Extended returns until Jan 31'])
            ),
            $section->block('second', '@awesome-theme/announcement', fn($block) => $block
                ->settings(['text' => 'Free delivery on orders over $50'])
            ),
        ])
    )
    ->order(['announcement-bar']);
```

The `section()` method accepts:

- A unique section key
- The section type (`@vendor/section-name`)
- A closure that configures the section's settings and blocks

---

Next: [Section Attributes](./section-attributes.md)

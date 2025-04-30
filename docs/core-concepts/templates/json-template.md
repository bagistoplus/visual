# JSON Templates

JSON (or YAML) templates define **how sections are organized on a page** in Bagisto Visual.

Rather than coding in PHP, templates use a simple configuration file that lists sections, their settings, and their order.
This allows **merchants** to **customize, add, remove, and reorder sections** easily through the **Theme Editor**.

JSON templates make themes **modular, flexible, and user-friendly** without touching code.

## How JSON Templates Work

- **Each page** has a template that defines which **sections** appear and in what **order**.
- **Each section** can have:
  - **Settings** (customizable fields for merchants).
  - **Blocks** (repeatable, configurable pieces inside a section).
- The **Theme Editor** reads the template and lets merchants **visually control** the storefront layout.

## Template Structure

A JSON (or YAML) template contains:

- A **sections** object — defines the sections on the page.
- An **order** array — determines the order sections are rendered.

Example basic structure:

```json
{
  "sections": {
    "hero": {
      "type": "visual-hero",
      "settings": {
        "image": "https://example.com/banner.jpg",
        "size": "medium"
      }
    }
  },
  "order": ["hero"]
}
```

## Example: Full Home Page Template

:::: tabs
::: tab JSON

```json
{
  "sections": {
    "hero": {
      "type": "visual-hero",
      "settings": {
        "image": "https://images.unsplash.com/photo-1441984904996-e0b6ba687e04",
        "size": "medium"
      },
      "blocks": {
        "heading": {
          "type": "heading",
          "settings": {
            "heading": "Talk about your brand"
          }
        },
        "text": {
          "type": "subheading",
          "settings": {
            "subheading": "Share details about your store"
          }
        },
        "button": {
          "type": "button",
          "settings": {
            "text": "Browse store",
            "link": "/",
            "style": "secondary"
          }
        }
      }
    },
    "category-list": {
      "type": "visual-category-list",
      "settings": {
        "heading": "Shop by category"
      },
      "blocks": {
        "category-1": {
          "type": "category",
          "settings": {
            "category": 2
          }
        },
        "category-2": {
          "type": "category",
          "settings": {
            "category": 3
          }
        }
      }
    },
    "featured-products": {
      "type": "visual-featured-products",
      "settings": {
        "heading": "Featured products",
        "product_type": "featured",
        "nb_products": 4
      }
    },
    "newsletter": {
      "type": "visual-newsletter"
    }
  },
  "order": ["hero", "category-list", "featured-products", "newsletter"]
}
```

:::

::: tab YAML

```yaml
sections:
  hero:
    type: visual-hero
    settings:
      image: https://images.unsplash.com/photo-1441984904996-e0b6ba687e04
      size: medium
    blocks:
      heading:
        type: heading
        settings:
          heading: Talk about your brand
      text:
        type: subheading
        settings:
          subheading: Share details about your store
      button:
        type: button
        settings:
          text: Browse store
          link: /
          style: secondary

  category-list:
    type: visual-category-list
    settings:
      heading: Shop by category
    blocks:
      category-1:
        type: category
        settings:
          category: 2
      category-2:
        type: category
        settings:
          category: 3

  featured-products:
    type: visual-featured-products
    settings:
      heading: Featured products
      product_type: featured
      nb_products: 4

  newsletter:
    type: visual-newsletter

order:
  - hero
  - category-list
  - featured-products
  - newsletter
```

:::
::::

## Section Schema

Each section object inside a template follows this structure:

:::: tabs

::: tab JSON

```json
{
  "<section-key>": {
    "type": "<section-type>",
    "settings": {
      "<setting-key>": "<value>"
    },
    "blocks": {
      "<block-key>": {
        "type": "<block-type>",
        "settings": {
          "<setting-key>": "<value>"
        }
      }
    }
  }
}
```

:::

::: tab YAML

```yaml
<section-key>:
  type: <section-type>
  settings:
    <setting-key>: <value>
    ...
  blocks:
    <block-key>:
      type: <block-type>
      settings:
        <setting-key>: <value>
        ...
```

:::

::::

### Description of fields:

| Field           | Required | Description                                                                                                                                                |
| --------------- | -------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **section-key** | Yes      | Unique ID or handle used in the template (e.g., `hero`, `category-list`).                                                                                  |
| **type**        | Yes      | Defines which section component to render (e.g., `visual-hero`, `visual-featured-products`).                                                               |
| **settings**    | Optional | Key-value pairs that configure how the section looks or behaves (e.g., text, images, colors).                                                              |
| **blocks**      | Optional | Sub-elements that can be repeated inside a section (e.g., items inside a list or slides inside a carousel). Each block can have its own type and settings. |

> **Note:**
> Blocks are optional — not all sections need to have blocks.

---

## Order

The `order` array defines **in which sequence** sections are displayed on the page.

- If a section is missing from the `order`, it **won't be rendered**.
- If `order` is not defined, Bagisto Visual will automatically use the default order of the sections as they appear in the sections object.
- If `order` is defined, it strictly controls the rendering sequence

> **Tip:**
> Defining an order manually is still recommended for better clarity, flexibility, and merchant control inside the Theme Editor.

## Working with the Theme Editor

When merchants open the Theme Editor:

- They can **rearrange sections** listed in `order`.
- They can **edit section settings**.
- They can **add or remove blocks** if the section supports it.

This gives merchants **complete control** over the page structure without touching code.

## Best Practices

- **Always define an `order`** matching your sections.
- **Use clear section names**.
- **Use settings and blocks** to maximize flexibility for merchants.
- **Prefer YAML** for better manual editing readability when templates grow.

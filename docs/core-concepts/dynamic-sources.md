# Dynamic Sources

Dynamic sources allow property values to be resolved from runtime context using `@path.to.value` syntax, enabling blocks and sections to access data from page variables, models, or parent-shared data.

## What Are Dynamic Sources?

Dynamic sources provide a way to bind block properties to runtime data without hardcoding values. Instead of static property values, you use the `@` prefix followed by a path to reference data from:

- **Page context** - Variables passed from controllers/views
- **Parent blocks/sections** - Data shared via the `share()` method
- **Models and objects** - Nested properties using dot notation
- **Arrays and collections** - Indexed or keyed access

**Static value** (hardcoded):
```json
{
    "title": "iPhone 15 Pro",
    "price": 999
}
```

**Dynamic source** (runtime):
```json
{
    "title": "@product.name",
    "price": "@product.price"
}
```

At runtime, `@product.name` resolves to the actual product name from your context.

## Basic Syntax

### Simple Path

Access a top-level context variable:

```json
{
    "userName": "@user"
}
```

```php
// Context
return view('page', ['user' => 'John Doe']);

// Resolves to
$block->settings->userName; // "John Doe"
```

### Dot Notation

Navigate nested objects and arrays:

```json
{
    "authorName": "@post.author.name",
    "authorEmail": "@post.author.email"
}
```

```php
// Context
$post = (object)[
    'author' => (object)[
        'name' => 'Jane Smith',
        'email' => 'jane@example.com'
    ]
];

// Resolves to
$block->settings->authorName;  // "Jane Smith"
$block->settings->authorEmail; // "jane@example.com"
```

### Array Access

Access array elements by index:

```json
{
    "firstImage": "@product.images.0.url",
    "secondImage": "@product.images.1.url"
}
```

```php
// Context
$product = [
    'images' => [
        ['url' => '/img1.jpg'],
        ['url' => '/img2.jpg'],
    ]
];

// Resolves to
$block->settings->firstImage;  // "/img1.jpg"
$block->settings->secondImage; // "/img2.jpg"
```

## Context Sources

### Page Context (Controller/View)

Pass data from controllers or views to templates:

```php
// ProductController.php
public function show(Product $product)
{
    return view('products.show', [
        'product' => $product,
        'relatedProducts' => $product->related()->take(4)->get(),
        'currency' => 'USD',
    ]);
}
```

In your JSON/YAML template:

```yaml
sections:
  - id: product-hero
    type: '@awesome-theme/product-hero'
    settings:
      name: '@product.name'
      price: '@product.price'
      description: '@product.description'
      currency: '@currency'
```

### Parent Shared Data

Parent blocks/sections share data with children using `share()`:

```php
class ProductCard extends SimpleBlock
{
    protected static array $accepts = [
        '@awesome-theme/product-image',
        '@awesome-theme/product-title',
        '@awesome-theme/product-price',
    ];

    public function share(): array
    {
        return [
            'product' => $this->block->settings->product ?? $this->context('product'),
            'showPrices' => $this->block->settings->showPrices ?? true,
        ];
    }
}
```

Child blocks access shared data using `@` syntax in presets:

```php
Preset::make('Product Card')
    ->blocks([
        PresetBlock::make('@awesome-theme/product-image')
            ->settings([
                'src' => '@product.base_image.url',
                'alt' => '@product.name',
            ]),
        PresetBlock::make('@awesome-theme/product-title')
            ->settings([
                'text' => '@product.name',
            ]),
        PresetBlock::make('@awesome-theme/product-price')
            ->settings([
                'amount' => '@product.price',
                'show' => '@showPrices',
            ]),
    ])
```

## Using in Setting Defaults

Dynamic sources can be used in setting defaults:

```php
public static function settings(): array
{
    return [
        Text::make('productName', 'Product Name')
            ->default('@product.name'),

        Number::make('price', 'Price')
            ->default('@product.price'),

        Image::make('image', 'Image')
            ->default('@product.base_image.url'),
    ];
}
```
---

**Next:** [Templates Overview](/core-concepts/templates/overview)

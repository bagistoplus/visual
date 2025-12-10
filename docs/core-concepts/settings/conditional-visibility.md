# Conditional Visibility

You can show or hide settings based on other setting values using `visibleWhen()`. This creates a cleaner, more intuitive settings panel by only displaying relevant options.

## Basic Usage

```php
Text::make('gridGap', 'Grid Gap')
    ->visibleWhen(fn($rule) => $rule->when('layout', 'grid'))
    ->default('1rem')
```

This setting will only be visible when the `layout` setting equals `'grid'`.

## Rule Methods

The callback receives a `Rule` instance with these methods:

### when()

Check if a field equals a value.

```php
->when(string $field, mixed $value)
```

**Example:**
```php
->visibleWhen(fn($rule) => $rule->when('layout', 'grid'))
```

### whenNot()

Check if a field does NOT equal a value.

```php
->whenNot(string $field, mixed $value)
```

**Example:**
```php
->visibleWhen(fn($rule) => $rule->whenNot('size', 'full'))
```

### whenIn()

Check if a field's value is in an array.

```php
->whenIn(string $field, array $values)
```

**Example:**
```php
->visibleWhen(fn($rule) => $rule->whenIn('layout', ['grid', 'flex']))
```

### whenNotIn()

Check if a field's value is NOT in an array.

```php
->whenNotIn(string $field, array $values)
```

**Example:**
```php
->visibleWhen(fn($rule) => $rule->whenNotIn('alignment', ['left', 'right']))
```

### whenGt() / whenLt()

Check if a field is greater than or less than a value.

```php
->whenGt(string $field, mixed $value)
->whenLt(string $field, mixed $value)
```

**Example:**
```php
->visibleWhen(fn($rule) => $rule->whenGt('columns', 1))
```

### whenTruthy() / whenFalsy()

Check if a field is truthy or falsy.

```php
->whenTruthy(string $field)
->whenFalsy(string $field)
```

**Example:**
```php
->visibleWhen(fn($rule) => $rule->whenTruthy('showAdvanced'))
```

## Complex Conditions

Combine multiple conditions with AND/OR logic:

### Multiple AND Conditions

Chain multiple `when()` calls for AND logic:

```php
Select::make('justifyContent', 'Justify Content')
    ->visibleWhen(fn($rule) => $rule
        ->when('layout', 'grid')
        ->whenGt('columns', 1)
    )
```

This setting is visible when `layout` is 'grid' AND `columns` is greater than 1.

### OR Logic

Use the `or()` method for OR conditions:

```php
Text::make('spacing', 'Spacing')
    ->visibleWhen(fn($rule) => $rule
        ->when('layout', 'grid')
        ->or(fn($r) => $r->when('type', 'image'))
    )
```

This setting is visible when `layout` is 'grid' OR `type` is 'image'.

### Nested Logic

Combine AND/OR for complex rules:

```php
Select::make('borderRadius', 'Border Radius')
    ->visibleWhen(fn($rule) => $rule
        ->whenIn('layout', ['grid', 'flex'])
        ->whenTruthy('showAdvanced')
        ->or(fn($r) => $r->when('type', 'card'))
    )
```

This setting is visible when:
- (`layout` is 'grid' OR 'flex' AND `showAdvanced` is true) OR
- (`type` is 'card')

## Real-World Examples

### Content Type Switcher

```php
public static function settings(): array
{
    return [
        Select::make('contentType', 'Content Type')
            ->options([
                'image' => 'Image',
                'video' => 'Video',
                'text' => 'Text',
            ])
            ->default('image'),

        Image::make('image', 'Image')
            ->visibleWhen(fn($rule) => $rule->when('contentType', 'image')),

        Text::make('videoUrl', 'Video URL')
            ->visibleWhen(fn($rule) => $rule->when('contentType', 'video')),

        Textarea::make('text', 'Text Content')
            ->visibleWhen(fn($rule) => $rule->when('contentType', 'text')),
    ];
}
```

### Layout-Specific Options

```php
public static function settings(): array
{
    return [
        Select::make('layout', 'Layout')
            ->options([
                'grid' => 'Grid',
                'list' => 'List',
                'masonry' => 'Masonry',
            ])
            ->default('grid'),

        Range::make('columns', 'Columns')
            ->visibleWhen(fn($rule) => $rule->whenIn('layout', ['grid', 'masonry']))
            ->min(1)
            ->max(6)
            ->default(3),

        Range::make('gap', 'Gap')
            ->visibleWhen(fn($rule) => $rule->when('layout', 'grid'))
            ->min(0)
            ->max(100)
            ->default(20),
    ];
}
```

### Advanced Settings Toggle

```php
public static function settings(): array
{
    return [
        Switch::make('showAdvanced', 'Show Advanced Settings')
            ->default(false),

        // Basic settings always visible
        Text::make('title', 'Title'),

        // Advanced settings only visible when toggle is on
        Range::make('maxWidth', 'Max Width')
            ->visibleWhen(fn($rule) => $rule->whenTruthy('showAdvanced'))
            ->min(200)
            ->max(1200),

        Select::make('animation', 'Animation')
            ->visibleWhen(fn($rule) => $rule->whenTruthy('showAdvanced'))
            ->options([
                'none' => 'None',
                'fade' => 'Fade',
                'slide' => 'Slide',
            ]),
    ];
}
```

## Best Practices

### Keep Conditions Simple

```php
// ✅ Clear and simple
->visibleWhen(fn($rule) => $rule->when('layout', 'grid'))

// ❌ Overly complex
->visibleWhen(fn($rule) => $rule
    ->when('layout', 'grid')
    ->or(fn($r) => $r->when('layout', 'flex'))
    ->whenGt('columns', 2)
    ->or(fn($r) => $r->whenTruthy('force'))
)
```

### Use Descriptive Field Names

```php
// ✅ Clear field names
->visibleWhen(fn($rule) => $rule->when('showAdvancedOptions', true))

// ❌ Unclear field names
->visibleWhen(fn($rule) => $rule->when('flag', true))
```

### Provide Default Values

Always set sensible defaults for conditionally visible settings, as they may be hidden when merchants first see the settings panel.

```php
Text::make('gridGap', 'Grid Gap')
    ->visibleWhen(fn($rule) => $rule->when('layout', 'grid'))
    ->default('1rem')  // ✅ Default provided
```

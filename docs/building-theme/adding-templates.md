# Adding Templates

Templates define the structure of store pages in Bagisto Visual.
They control how sections are arranged and rendered inside the main layout.

Templates can be created using two formats:

- **Blade templates** (`.blade.php`) — simple static content or basic Blade directives
- **JSON templates** (`.json` or `.yaml`) — fully dynamic, section-driven layouts for the Visual Editor

At this stage, we will start with a Blade template for simplicity.

## Creating a Blade Template

Blade templates are simple files that contain the page content.
They are injected into the layout through `@visual_layout_content`.

To create a homepage template:

1. Create a new file:

```text
resources/views/templates/index.blade.php
```

2. Add the following example content:

```blade
<div class="text-center py-12">
  <h1 class="text-4xl font-bold">Welcome to Awesome Theme</h1>
  <p class="mt-4 text-lg text-gray-600">Your new store is ready to be customized.</p>
</div>
```

This content will be rendered inside your theme’s default layout.

There is no need to use `@extends` or `@section`.

## Viewing the Template

Once the template file is created:

- Visit your store homepage in the browser.
- You should see the header and footer rendered from the layout.
- The page body will display the content from `index.blade.php`.

At this stage, no sections are included yet.
Templates are static until sections are added.

## JSON Templates

Bagisto Visual also supports creating templates using JSON files.
JSON templates enable merchants to visually edit pages, add sections dynamically, and control page structure without touching code.

The structure and behavior of JSON templates are explained in the [JSON Template](../core-concepts/templates/json-template.md) documentation.

In the next chapter, we will cover:

- How to create sections
- How to use sections inside JSON templates to build dynamic pages

For now, starting with a simple Blade template is sufficient to initialize your theme.

## Summary

- Templates define page content and are rendered inside layouts.
- Blade templates are static and easy to start with.
- JSON templates enable full dynamic editing and will be introduced after learning about sections.
- Templates must be placed in `resources/views/templates/`.

Read more about [available default templates](../core-concepts/templates/available.md)

## Next Steps

- [Adding Sections](./adding-sections/overview.md)

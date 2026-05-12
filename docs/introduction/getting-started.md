# Getting Started

Bagisto Visual gives you a full visual editing experience for your storefront — with real-time preview, drag-and-drop sections, and theme customization.

This guide will help you:

1. Install Bagisto Visual
2. Add the default starter theme
3. Launch the visual editor
4. Add and edit content
5. Create your first custom section

## Prerequisites

Before you begin, make sure you have:

- PHP version **8.2 or later**
- A running Bagisto store (version **2.3 or later**)

## Step 1: Install Bagisto Visual

Install the package via Composer:

::: info
If you're installing an alpha/beta version, ensure your project accepts dev packages:

```bash
composer config minimum-stability dev && composer config prefer-stable true
```

This allows Composer to install pre-release versions of Bagisto Visual.
:::

```bash
composer require bagistoplus/visual:^2.0@dev
```

Then publish the assets:

```bash
php artisan vendor:publish --tag=visual-assets
```

Then run the package migrations:

```bash
php artisan migrate
```

This installs Bagisto Visual, creates the Visual-owned tables required by editor and admin features, and prepares your store for theme customization.

## Step 2: Install the Starter Theme

Install the default theme package:

```bash
composer require bagistoplus/visual-debut:^2.0@dev
```

And publish its assets:

```bash
php artisan vendor:publish --tag=visual-debut-assets
```

Once installed, the theme will appear in your Bagisto admin under the menu **Bagisto Visual** -> **Themes**.

Click **Customize** to launch the visual editor.

## Step 3: Launch the Visual Editor

From the admin panel, navigate to **Bagisto Visual** → **Themes** and click **Customize** on the Visual Debut theme.

**The editor interface has three main areas:**

- **Left Sidebar** – Browse pages, add sections, manage content structure
- **Center Preview** – Live preview of your storefront with real-time updates
- **Right Panel** – Edit settings for the selected section or block

**Editor tools:**

- **Page Selector** – Switch between Home, Product, Collection, and other pages
- **Device Preview** – Test mobile, tablet, and desktop layouts
- **Language Switcher** – Customize content for different locales
- **Save** – Save your work as draft
- **Publish** – Make changes live on your storefront

::: tip
Changes in the editor are not visible to customers until you click **Publish**. This lets you experiment freely.
:::

> ℹ️ For a full breakdown of the interface, see [Theme Editor: Interface Guide](../theme-editor/interface-guide.md)

## Step 4: Add and Edit Content

### Adding Sections

Sections are the building blocks of your pages. To add a new section:

1. Click **Add Section** in the left sidebar
2. Browse available sections (Hero, Newsletter, Featured Products, etc.)
3. Click a section to add it to your page
4. The section appears in both the preview and sidebar

### Customizing Sections

Once a section is added:

1. **Click the section** in the preview or sidebar to select it
2. The right panel shows available settings:
   - Text fields for headings and descriptions
   - Image uploads for backgrounds and media
   - Color pickers for styling
   - Link fields for buttons and CTAs
   - Layout options for columns and spacing
3. **Changes appear instantly** in the preview
4. **Drag sections** in the sidebar to reorder them

### Working with Blocks

In v2 themes, sections can contain blocks — reusable components you can add, remove, and rearrange:

- Click **Add Block** within a section to add components
- Drag blocks to reorder them
- Each block has its own settings
- Blocks can be nested (columns inside tabs, galleries inside accordions)

::: tip
Use the **undo/redo** buttons if you make a mistake. Your changes are only saved when you click **Save** or **Publish**.
:::

## Step 5: Create a Custom Section

Ready to build your own sections? Use the Artisan command:

```bash
php artisan visual:make-section MyBanner
```

**This generates two files:**

1. **PHP class:** `app/Visual/Sections/MyBanner.php`

   - Defines section settings (text fields, images, selects)
   - Configures which blocks it accepts
   - Controls section behavior

2. **Blade template:** `resources/views/sections/my-banner.blade.php`
   - The HTML structure of your section
   - Access settings via `$section` variable
   - Render child blocks with `@children`

**Example section class:**

```php
public static function settings(): array
{
    return [
        Text::make('heading', 'Heading')->default('Welcome'),
        Textarea::make('description', 'Description'),
        Image::make('background', 'Background Image'),
    ];
}
```

Once created, your section will appear in the **Add Section** menu in the editor.

> 🧱 For complete guides on building sections and blocks, see:
>
> - [Creating Sections](../building-theme/adding-sections/creating-section)
> - [Creating Blocks](../building-theme/adding-blocks/creating-block)

## Troubleshooting

### Composer installation fails

**Check minimum stability settings:**

```bash
composer config minimum-stability dev
composer config prefer-stable true
```

### Assets not loading

**Republish assets:**

```bash
php artisan vendor:publish --tag=visual-assets --force
php artisan vendor:publish --tag=visual-debut-assets --force
```

## What's Next?

**Learn the fundamentals:**

- [Sections](../core-concepts/sections) – Understand sections
- [Blocks](../core-concepts/blocks) – Understand blocks
- [Theme Editor Guide](../theme-editor/interface-guide) – Master the visual editor

**Start building:**

- [Creating Sections](../building-theme/adding-sections/creating-section) – Build custom sections
- [Creating Blocks](../building-theme/adding-blocks/creating-block) – Create reusable blocks
- [Settings Guide](../core-concepts/settings/overview) – Add configuration options

---

You're ready to build a beautiful, customized storefront — visually.

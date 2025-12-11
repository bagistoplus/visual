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

```bash
composer require bagistoplus/visual:^1.0
```

Then publish the assets:

```bash
php artisan vendor:publish --tag=visual-assets
```

This installs the Bagisto Visual and prepares your store for theme customization.

## Step 2: Install the Starter Theme

Install the default theme package:

```bash
composer require bagistoplus/visual-debut
```

And publish it's assets:

```bash
php artisan vendor:publish --tag=visual-debut-assets
```

Once installed, the theme will appear in your Bagisto admin under the menu **Bagisto Visual** -> **Themes**.

Click **Customize** to launch the visual editor.

## Step 3: Launch the Visual Editor

When you open the editor, you’ll see:

- A live preview of your storefront
- Sidebar with section layout and content
- Tools to change language, screen size, and pages

You can now:

- Add new sections to the page
- Rearrange them
- Click a section to edit its settings

> ℹ️ For a full breakdown of the interface, see [Theme Editor: Interface Guide](../theme-editor/interface-guide.md)

## Step 4: Add and Edit Sections

To add a new section:

1. Click **Add Section** in the sidebar
2. Choose a section like **Hero**, **Newsletter**, or **Featured Products**
3. Click on the section to customize its settings (text, images, links)
4. Changes appear instantly in the preview

## Step 5: Create a Custom Section

To build your own section, use the following Artisan command:

```bash
php artisan visual:make-section MyBanner
```

This creates:

- A PHP class at: `app/Visual/Sections/MyBanner.php`
- A Blade file at: `resources/views/sections/my-banner.blade.php`

You can now edit the view and class to define your own settings and layout.

> 🧱 To go deeper, see [Creating Sections](../building-theme/adding-sections/overview.md)

## What’s Next?

- [Learn more about the architecture](../core-concepts/architecture.md)
- [Learn to Build Sections](../building-theme/adding-sections/overview.md)

---

You’re ready to build a beautiful, customized storefront — visually.

# Creating a New Theme

Bagisto Visual makes it easy to create a new theme using a single command.

## Step 1: Generate a Theme

To scaffold a brand new theme, run the following Artisan command:

```bash
php artisan visual:make-theme "Awesome Theme"
```

By default, the theme will be placed under the `Themes` vendor namespace.

---

### Optional: Customize the Vendor Name

You can also customize the vendor (namespace) if needed:

```bash
php artisan visual:make-theme "Awesome Theme" --vendor="MyVendor"
```

This will generate the theme inside:

```text
src/Packages/MyVendor/AwesomeTheme/
```

If the `--vendor` option is not provided, it defaults to:

```text
src/Packages/Themes/AwesomeTheme/
```

✅ This is useful if you're developing themes under different brand or client namespaces.

## Step 2: Theme Directory Structure

Here’s what a freshly generated theme looks like:

```text
AwesomeTheme/
├── config/
│   ├── settings.php           # Global theme settings (colors, fonts, social links, etc.)
│   └── theme.php              # Main theme metadata (name, author, version)
├── resources/
│   ├── assets/
│   │   └── images/
│   │       └── theme-preview.png # Theme preview image
│   └── views/
│       ├── components/         # Blade components used inside sections
│       ├── layouts/            # Main layouts (default.blade.php, account.blade.php)
│       ├── sections/           # Blade views for each section
│       └── templates/          # Page templates (Blade, JSON, or YAML)
├── src/
│   ├── Sections/               # PHP classes defining sections and blocks
│   │   ├── ExampleSection.php
│   │   └── ...
│   └── ServiceProvider.php     # Registers the theme into Bagisto Visual
├── package.json                # Frontend dependencies and build scripts
├── composer.json               # PHP package metadata for autoloading
├── tailwind.config.js          # TailwindCSS configuration (optional)
├── vite.config.ts              # ViteJS configuration for assets
└── README.md                   # Theme documentation (optional)
```

✅
The `theme-preview.png` file will be used to visually represent your theme in the Theme Editor.

### Understanding Important Files

#### `config/theme.php`

Defines core metadata and configuration for your theme:

```php
<?php

return [
    "code" => "awesome-theme",
    "name" => "Awesome Theme",
    "version" => "1.0.0",
    "author" => "Your Company Name",
    "assets_path" => "public/themes/shop/awesome-theme",
    "views_path" => "resources/themes/awesome-theme/views",
    "preview_image" => "public/themes/shop/awesome-theme/preview.png",
    "documentation_url" => "https://yourdomain.com/docs/themes/awesome-theme",

    "vite" => [
        "hot_file" => "awesome-theme-vite.hot",
        "build_directory" => "themes/awesome-theme/dist",
        "package_assets_directory" => "resources/assets"
    ]
];
```

#### `config/settings.php`

Defines editable theme-level settings such as:

- Colors
- Fonts
- Header and footer links
- Social icons

These settings are exposed in the Theme Editor and configurable by merchants.

#### `resources/views/layouts/`

Contains layout Blade files like `default.blade.php`.
All pages render inside one of these layouts.

#### `resources/views/templates/`

Templates define full pages and reference a layout and a list of sections.
They can be Blade, JSON, or YAML.

#### `resources/views/sections/`

Contains section Blade files.
Each section is a reusable UI block that can be added to templates in the visual editor.

### Starting From a Starter Theme

By default, the scaffold generates a **blank theme** with no layout, sections, or styles.

If you prefer to start with a **sensible base theme**, you can install the official Bagisto Visual starter theme:

```bash
composer require bagistoplus/visual-debut
```

Then, inside your `config/theme.php`, set your theme to **extend** the starter theme by adding:

```php
'parent' => 'visual-debut',
```

✅
This way, your theme will inherit everything from **Visual Debut**, and you only need to **override or customize** what you want.

✅
However, you are also **free to build a theme completely from scratch** if you prefer a fully custom structure.

## Step 3: Installing and Using the Theme

Once the theme is generated, it is automatically configured inside your Bagisto project using Composer's local repository system.

You can immediately install the theme by running:

```bash
composer require themes/awesome-theme
```

✅
No need to publish the theme package to Packagist or any remote server.

You only need to publish the package if you intend to distribute or share it outside your current project.

## Step 4: Activate the Theme in Admin

After installation, activate your theme in the admin panel:

1. Log into the Bagisto admin panel.
2. Go to **Settings → Channels**.
3. Select the channel (e.g., `default`).
4. Under the **Design** section, choose **Awesome Theme** from the **Theme** dropdown.
5. Click **Save Channel**.

✅
Your storefront is now using the new theme.

## Next Steps

Once your theme is generated, you can continue with:

- [Adding Layouts](./adding-layouts.md)
- [Creating Templates](./adding-templates.md)
- [Creating Sections](./adding-sections/overview.md)
- [Configuring Theme Settings](../core-concepts/settings/theme-settings.md)

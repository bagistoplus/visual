# Theme Settings

## Introduction

Theme settings allow developers to define global customization options for a theme.
These settings are available in the **Theme Editor** for merchants to modify things like colors, typography, social media links, and more.

Theme settings are defined in a `settings.php` file located here:

```text
theme/
├── config/
│   └── settings.php
├── resources/
├── src/
├── ...
```

They provide a centralized way to control the visual style and key configuration of the entire storefront.

## File Structure

Each theme settings file must return an **array of setting groups**.
Each group contains:

- `name`: The group title shown inside the theme editor.
- `settings`: An array of Setting types (`Color`, `Text`, `Font`, etc.) available for merchants to configure.

Example file:

```php
<?php

use BagistoPlus\Visual\Settings;
use function BagistoPlus\Visual\t;

return [
  [
    'name' => t('awesome-theme::settings.groups.light_scheme'),
    'settings' => [
      Settings\Color::make('light_background_color', t('awesome-theme::settings.light_background_color'))
        ->default('#ffffff')
        ->info(t('awesome-theme::settings.light_background_color_info')),

      Settings\Color::make('light_primary_color', t('awesome-theme::settings.light_primary_color'))
        ->default('#92400e')
        ->info(t('awesome-theme::settings.light_primary_color_info')),

      // More color settings...
    ],
  ],
  [
    'name' => t('awesome-theme::settings.groups.typography'),
    'settings' => [
      Settings\Font::make('heading_font', t('awesome-theme::settings.heading_font'))
          ->default('Inter'),

      Settings\Font::make('body_font', t('awesome-theme::settings.body_font'))
          ->default('Roboto'),
    ],
  ],
  [
    'name' => t('awesome-theme::settings.groups.social_links'),
    'settings' => [
      Settings\Text::make('facebook_url', t('awesome-theme::settings.facebook_url'))
        ->default('https://www.facebook.com'),

      Settings\Text::make('instagram_url', t('awesome-theme::settings.instagram_url'))
        ->default('https://www.instagram.com'),

      Settings\Text::make('youtube_url', t('awesome-theme::settings.youtube_url'))
        ->default('https://www.youtube.com'),

      Settings\Text::make('tiktok_url', t('awesome-theme::settings.tiktok_url'))
        ->default('https://www.tiktok.com'),

      Settings\Text::make('twitter_url', t('awesome-theme::settings.twitter_url'))
        ->default('https://www.x.com'),

      Settings\Text::make('snapchat_url', t('awesome-theme::settings.snapchat_url'))
        ->default('https://www.snapchat.com'),
    ],
  ],
];
```

Use Visual's `t()` helper for group names, labels, helper text, and default text that should adapt to the active locale until the merchant customizes it.

`Text`, `Textarea`, and `RichText` settings are localized by default. Other setting types can opt in with `->localized()`.

## Accessing Theme Settings in Blade

Theme settings are automatically injected into every view through the `$theme->settings` object.

Example:

```blade
<body style="background-color: {{ $theme->settings->light_background_color }};">
    <!-- Storefront content -->
</body>
```

## Best Practices

- Always group related settings meaningfully for a better merchant experience.
- Use informative labels and helper texts (`info()`) to guide merchants.
- Provide sensible default values for each setting to ensure good first impressions.
- Use consistent naming conventions (`light_primary_color`, `color_body_text`, etc.).

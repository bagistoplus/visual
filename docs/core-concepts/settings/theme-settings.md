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

return [
  [
    'name' => 'Light Scheme Colors (default)',
    'settings' => [
      Settings\Color::make('light_background_color', 'Background')
        ->default('#ffffff')
        ->info('Default page color. Used for main page background and large content areas'),

      Settings\Color::make('light_primary_color', 'Primary')
        ->default('#92400e')
        ->info('Main brand color. Used for buttons and key elements'),

      // More color settings...
    ],
  ],
  [
    'name' => 'Typography',
    'settings' => [
      Settings\Font::make('heading_font', 'Heading Font')
          ->default('Inter'),

      Settings\Font::make('body_font', 'Body Font')
          ->default('Roboto'),
    ],
  ],
  [
    'name' => 'Social Media Links',
    'settings' => [
      Settings\Text::make('facebook_url', 'Facebook URL')
        ->default('https://www.facebook.com'),

      Settings\Text::make('instagram_url', 'Instagram URL')
        ->default('https://www.instagram.com'),

      Settings\Text::make('youtube_url', 'YouTube URL')
        ->default('https://www.youtube.com'),

      Settings\Text::make('tiktok_url', 'TikTok URL')
        ->default('https://www.tiktok.com'),

      Settings\Text::make('twitter_url', 'X/Twitter URL')
        ->default('https://www.x.com'),

      Settings\Text::make('snapchat_url', 'Snapchat')
        ->default('https://www.snapchat.com'),
    ],
  ],
];
```

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

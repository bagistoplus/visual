<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Where theme data is kept, as one of the storages below. "file" keeps it
    | in files. "database" keeps it in the visual_theme_* tables, for hosts
    | whose instances share no filesystem.
    |
    */
    'storage' => env('BAGISTO_VISUAL_STORAGE', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Storages
    |--------------------------------------------------------------------------
    |
    | Settings for each storage. The file storage keeps theme data under its
    | path. The database storage uses the given connection and cache store,
    | or the app's defaults when they are null. Every instance must share
    | that cache store, such as Redis, so a publish reaches all of them.
    |
    */
    'storages' => [
        'file' => [
            'path' => storage_path('bagisto-visual'),
        ],

        'database' => [
            'connection' => env('BAGISTO_VISUAL_DB_CONNECTION'),
            'cache' => env('BAGISTO_VISUAL_CACHE_STORE'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Versions To Keep
    |--------------------------------------------------------------------------
    |
    | Each publish snapshots the theme data under versions/. Only the newest
    | versions are kept; set to 0 to keep none.
    |
    */
    'versions_to_keep' => env('BAGISTO_VISUAL_VERSIONS_TO_KEEP', 5),

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    |
    | Storage settings for theme images.
    |
    */
    'images' => [
        'storage' => 'public',
        'directory' => 'bagisto-visual/images',

        /*
         * WebP copies made of each uploaded image, at the widths below the
         * original's, so a template can serve the size a screen needs.
         */
        'variants' => [
            'widths' => [320, 480, 640, 768, 1024, 1280, 1600, 1920],
            'quality' => 80,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Videos
    |--------------------------------------------------------------------------
    |
    | Storage settings for theme videos.
    |
    */
    'videos' => [
        'storage' => 'public',
        'directory' => 'bagisto-visual/videos',
        'max_upload_size' => 51200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Assignments
    |--------------------------------------------------------------------------
    |
    | Enable persistent template assignments for products, categories, and CMS
    | pages. This feature requires the visual_template_assignments table.
    |
    */
    'template_assignments' => env('BAGISTO_VISUAL_TEMPLATE_ASSIGNMENTS', false),

    /*
    |--------------------------------------------------------------------------
    | Theme Settings Cache TTL
    |--------------------------------------------------------------------------
    |
    | Cache TTL for theme settings in seconds.
    | Default: 86400 (1 day)
    | Set to 0 to disable caching.
    |
    */
    'settings_cache_ttl' => env('BAGISTO_VISUAL_SETTINGS_CACHE_TTL', 86400),
];

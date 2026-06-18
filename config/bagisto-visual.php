<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Data Path
    |--------------------------------------------------------------------------
    |
    | The directory where theme data files are stored.
    |
    */
    'data_path' => storage_path('bagisto-visual'),

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

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
    | Images Storage
    |--------------------------------------------------------------------------
    |
    | The storage disk to use for theme images.
    |
    */
    'images_storage' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Images Directory
    |--------------------------------------------------------------------------
    |
    | The directory path within the storage disk for theme images.
    |
    */
    'images_directory' => 'bagisto-visual/images',

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

# Versioning Visual Changes

Bagisto Visual stores editor changes on disk. By default, those files live in `storage/bagisto-visual`, which is usually ignored by git and local to one environment.

If you want Visual changes to move from local development to staging and production through your deployment pipeline, move the Visual data path into a repo-tracked directory. Uploaded media can either be committed with the project or stored on shared persistent storage.

## What Visual Stores

Visual data is controlled by `data_path`:

```php
'data_path' => storage_path('bagisto-visual'),
```

This directory contains the theme state that the editor writes, including editor and live template/settings data. It is separate from uploaded media.

Uploads are controlled by Laravel filesystem disks:

```php
'images' => [
    'storage' => 'public',
    'directory' => 'bagisto-visual/images',
],

'videos' => [
    'storage' => 'public',
    'directory' => 'bagisto-visual/videos',
    'max_upload_size' => 51200,
],
```

## Fully Versioned Workflow

This workflow keeps both Visual data and uploaded media in your repository. It is useful for small teams, curated demo stores, and projects where uploaded media is part of the theme source.

Publish the Visual configuration file if it is not already published:

```bash
php artisan vendor:publish --tag=visual-config
```

Then update `config/bagisto_visual.php`:

```php
'data_path' => base_path('visual/data'),

'images' => [
    'storage' => 'visual',
    'directory' => 'images',
],

'videos' => [
    'storage' => 'visual',
    'directory' => 'videos',
    'max_upload_size' => 51200,
],
```

Add a `visual` disk to `config/filesystems.php`:

```php
'disks' => [
    // ...

    'visual' => [
        'driver' => 'local',
        'root' => base_path('visual/uploads'),
        'url' => env('APP_URL').'/visual-uploads',
        'visibility' => 'public',
        'throw' => false,
    ],
],
```

Add the public link for that disk in the same file:

```php
'links' => [
    public_path('storage') => storage_path('app/public'),
    public_path('visual-uploads') => base_path('visual/uploads'),
],
```

Create the directories:

```bash
mkdir -p visual/data visual/uploads/images visual/uploads/videos
```

Expose the uploads publicly:

```bash
php artisan storage:link
```

Add the local image upload path to `config/imagecache.php` so Bagisto can generate cached image variants from Visual uploads:

```php
'paths' => [
    // ...
    base_path('visual/uploads/images'),
],
```

Commit the `visual` directory:

```bash
git add visual
git commit -m "chore: version visual data and uploads"
```

This makes editor data and uploaded media deploy with the application. The tradeoff is repository growth, especially when videos are uploaded.

## Recommended Production Workflow

For production teams, keep Visual data in git and store uploads on shared persistent storage. This avoids large media files in the repository and makes uploads available across all servers.

Keep the Visual data path in the repository:

```php
'data_path' => base_path('visual/data'),
```

Configure an S3-compatible disk in `config/filesystems.php`:

```php
'disks' => [
    // ...

    'visual_uploads' => [
        'driver' => 's3',
        'key' => env('VISUAL_UPLOADS_ACCESS_KEY_ID'),
        'secret' => env('VISUAL_UPLOADS_SECRET_ACCESS_KEY'),
        'region' => env('VISUAL_UPLOADS_DEFAULT_REGION', 'auto'),
        'bucket' => env('VISUAL_UPLOADS_BUCKET'),
        'url' => env('VISUAL_UPLOADS_URL'),
        'endpoint' => env('VISUAL_UPLOADS_ENDPOINT'),
        'use_path_style_endpoint' => env('VISUAL_UPLOADS_USE_PATH_STYLE_ENDPOINT', false),
        'throw' => false,
    ],
],
```

Then use that disk for images and videos:

```php
'images' => [
    'storage' => 'visual_uploads',
    'directory' => 'images',
],

'videos' => [
    'storage' => 'visual_uploads',
    'directory' => 'videos',
    'max_upload_size' => 51200,
],
```

Commit only the Visual data:

```bash
git add visual/data
git commit -m "chore: version visual data"
```

Your deployment now carries layout, template, and setting changes through git, while uploaded images and videos remain on persistent shared storage.

## Migrating Existing Data

Back up your project and storage before migrating existing Visual data or uploads.

Copy existing Visual data into the repo-tracked path:

```bash
mkdir -p visual/data
cp -R storage/bagisto-visual/. visual/data/
```

For the fully versioned workflow, copy existing uploads into the `visual` disk root:

```bash
mkdir -p visual/uploads/images visual/uploads/videos
cp -R storage/app/public/bagisto-visual/images/. visual/uploads/images/
cp -R storage/app/public/bagisto-visual/videos/. visual/uploads/videos/
```

For the S3-compatible workflow, upload existing files to the bucket paths that match your configured directories:

```text
images/*
videos/*
```

After migration, open the Visual editor in each environment and confirm that templates, settings, images, and videos resolve correctly.

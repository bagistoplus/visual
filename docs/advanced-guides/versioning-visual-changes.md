# Versioning Visual Changes

By default, Bagisto Visual stores editor changes in files, using the `file` storage. Those files live in `storage/bagisto-visual`, which is usually ignored by git and local to one environment.

If you want Visual changes to move from local development to staging and production through your deployment pipeline, move the Visual data path into a repo-tracked directory. Uploaded media can either be committed with the project or stored on shared persistent storage.

## Choosing an Approach

Versioning Visual data in git fits when every layout change is made outside production, locally or on staging, and production only receives changes through deploys. In that setup, treat the production editor as read only.

If merchants or your team edit in the production editor, or production runs on more than one instance (Laravel Cloud, autoscaled servers), we recommend [Database Storage](./database-storage.md) for production instead. Theme data then lives in your database, every instance sees the same edits, and deploys never touch it. You can still [seed it once from versioned files](#seeding-database-storage-from-versioned-files).

Whichever storage you choose, keep uploaded images and videos on shared persistent storage in production, as described in [Versioned Data with Shared Uploads](#versioned-data-with-shared-uploads).

## What Visual Stores

Visual data is controlled by the path of the `file` storage:

```php
'storage' => env('BAGISTO_VISUAL_STORAGE', 'file'),

'storages' => [
    'file' => [
        'path' => storage_path('bagisto-visual'),
    ],
],
```

This directory contains the theme state that the editor writes, including editor and live template/settings data. It is separate from uploaded media.

The workflows in this guide require the `file` storage. If `BAGISTO_VISUAL_STORAGE` is set to `database`, theme data lives in the database and the file path is ignored, so committing it changes nothing.

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
'storages' => [
    'file' => [
        'path' => base_path('visual/data'),
    ],
    // ...
],

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

::: warning Edits made in production
Edits saved in the production editor are written to `visual/data` on that server only. The next deploy overwrites them or conflicts with them, and on multi instance hosts they only reach the instance that handled the request. If production needs to be edited in place, use [Database Storage](./database-storage.md).
:::

## Versioned Data with Shared Uploads

For production teams using git versioning, keep Visual data in git and store uploads on shared persistent storage. This avoids large media files in the repository and makes uploads available across all servers.

Keep the Visual data path in the repository:

```php
'storages' => [
    'file' => [
        'path' => base_path('visual/data'),
    ],
    // ...
],
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

::: warning Edits made in production
The same rule applies here: edits saved in the production editor do not survive the next deploy and are not shared between instances. If production needs to be edited in place, use [Database Storage](./database-storage.md).
:::

## Seeding Database Storage from Versioned Files

If you developed a theme with versioned files and want production on database storage, import the committed data once. `visual:storage:import` reads from `storages.file.path`, so with the path set to `base_path('visual/data')` it copies the files your deploy shipped:

```bash
php artisan migrate --force
php artisan visual:storage:import
```

Then set `BAGISTO_VISUAL_STORAGE=database`. From that point on, production is edited in place and `visual/data` no longer affects it. See [Database Storage](./database-storage.md#switching-to-the-database) for the full setup.

Treat this as a one time migration, not a deploy step. Without `--force`, the command stops if the database already holds the theme. With `--force`, it replaces the editor documents and publishes the files as a new live version, which discards edits made in production since the last import. Previously published versions stay in the database up to `versions_to_keep`, but unpublished editor changes are lost.

## Migrating Existing Data

Back up your project and storage before migrating existing Visual data or uploads.

Copy existing Visual data into the repo-tracked path:

```bash
mkdir -p visual/data
cp -R storage/bagisto-visual/. visual/data/
```

If the project currently uses database storage, export the theme data into the repo-tracked path instead. Set `storages.file.path` to `base_path('visual/data')` first, since the command writes there, then run:

```bash
php artisan visual:storage:export
```

Then set `BAGISTO_VISUAL_STORAGE=file`. See [Import and Export](./database-storage.md#import-and-export) for the available options.

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

If your app uses [storage scopes](./storage-scopes.md), uploads are stored under the scope name, for example `storage/app/public/acme/bagisto-visual/images` by default and `acme/images/*` with the directories above. Copy each scope's uploads to the matching scoped path. Visual data needs no extra step, because the data path already contains one directory per scope.

After migration, open the Visual editor in each environment and confirm that templates, settings, images, and videos resolve correctly.

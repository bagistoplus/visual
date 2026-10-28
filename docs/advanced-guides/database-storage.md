# Database Storage

By default, Bagisto Visual keeps the editor's theme data in files. That works as long as every server reads and writes the same disk. On hosts where each instance has its own filesystem, such as Laravel Cloud or an autoscaled fleet, an edit saved on one instance is invisible to the others. The database storage keeps theme data in your database instead, so every instance sees the same thing.

File storage stays the default and keeps working as before. Choose one storage for the whole app.

## Requirements

- A database every instance connects to. Any database Laravel supports works.
- A cache store every instance shares, such as Redis, Memcached or Laravel's `database` cache. Instances learn about each other's edits and publishes through it. The `file` and `array` stores are local to one instance and do not work here.

## Configuration

Two keys in `config/bagisto_visual.php` pick and configure the storage:

```php
'storage' => env('BAGISTO_VISUAL_STORAGE', 'file'),

'storages' => [
    'file' => [
        'path' => storage_path('bagisto-visual'),
    ],

    'database' => [
        'connection' => env('BAGISTO_VISUAL_DB_CONNECTION'),
        'cache' => env('BAGISTO_VISUAL_CACHE_STORE'),
    ],
],
```

`connection` is the database connection the theme data tables live on, and `cache` is the cache store Visual uses for them. Leave either one empty to use your app's default connection or cache store. Setting `cache` lets your app keep its own default cache while Visual uses a shared one:

```ini
BAGISTO_VISUAL_STORAGE=database
BAGISTO_VISUAL_CACHE_STORE=redis
```

::: info Upgrading from `data_path`
Older versions configured the file storage with a top level `data_path` key. It is no longer read. If your published config sets it, move its value to `storages.file.path`.
:::

## Switching to the Database

1. Run the migration, which creates the tables on the configured connection:

   ```bash
   php artisan migrate
   ```

2. Copy your existing theme data from files into the database:

   ```bash
   php artisan visual:storage:import
   ```

3. Set `BAGISTO_VISUAL_STORAGE=database` and deploy.

Uploaded images and videos are not theme data. They live on the filesystem disks set under `images` and `videos`, so put those on shared storage too, for example an S3 disk. See [Versioning Visual Changes](./versioning-visual-changes.md#recommended-production-workflow) for an example.

## How It Works

Visual stores three things per theme:

- **Editor documents**, one row per document, in `visual_theme_documents`. This is what the editor works on.
- **Versions**, in `visual_theme_versions`. Each publish stores every published document as one version. The newest version is what the storefront serves.
- **State**, in `visual_theme_states`: which version is live, how many edits the editor has seen, and whether some of them are unpublished.

Most reads are served from the shared cache. A storefront request on a warm cache costs two cache round trips and no database queries. A theme's current editor and live versions are cached for five minutes, and every write replaces them at once. Versions and document contents never change once written, so they are cached for a day. If the cache is cleared or loses entries, Visual reads them from the database again.

`versions_to_keep` applies as in file storage, except that the live version is always kept.

## Import and Export

`visual:storage:import` copies theme data from files into the database, and `visual:storage:export` copies it from the database back into files. Both work whichever storage the app is set to use.

```bash
php artisan visual:storage:import
php artisan visual:storage:export --theme=visual-debut
php artisan visual:storage:import --scope=acme --force
```

| Option | Effect |
|---|---|
| `--theme=` | Copy only this theme. By default, every theme the source holds is copied. |
| `--scope=` | Copy the data of this [storage scope](./storage-scopes.md). By default, the data with no scope is copied. |
| `--force` | Replace theme data the target already holds. Without it, the command stops before writing anything if any theme it would copy is already there. |

For each theme, the command copies the editor documents, the live documents and whether the editor holds unpublished changes. The editor documents replace the target's. The live documents are published in the target as a new version, so the versions it already held are kept and you can still go back. A theme that was never published only has its editor documents copied, and what the target serves for it stays as it was.

Older versions are not copied: the target starts with the source's live documents as its newest version.

After copying, the command clears the theme settings cache and the full page cache, as publishing does.

## Things to Know

- `php artisan visual:storage:clear` empties the theme data tables when the database storage is active.
- Two people editing the same document at the same time overwrite each other: the last save wins. This is the same as with file storage.
- Each edit and publish makes a few database and cache round trips. On a database a few milliseconds away, saving or publishing takes roughly 30 to 100 ms longer than with files. Storefront pages are not measurably slower.

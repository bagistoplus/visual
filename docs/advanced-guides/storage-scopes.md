# Storage Scopes

A storage scope keeps Visual's data apart for different groups of users, such as the tenants of a multi tenant app. Each scope has its own theme data, its own cached theme settings and its own uploaded media, so what one tenant edits and publishes never shows up for another.

Visual does not know about tenants. Your app tells it which scope the current request belongs to.

## Setting the Scope

Register a resolver in a service provider's `boot` method:

```php
use BagistoPlus\Visual\Facades\Visual;

public function boot(): void
{
    Visual::resolveStorageScopeUsing(fn () => tenant()?->id);
}
```

Visual calls the resolver every time it needs the scope, so it follows whatever tenant your app has switched to. Return `null` or an empty string for no scope.

A scope becomes a directory name and part of cache keys. It may only contain letters, numbers, dots, dashes and underscores, and must start with a letter or a number. Visual throws an exception for anything else.

## What a Scope Separates

| Data                         | Without a scope            | With scope `acme`            |
| ---------------------------- | -------------------------- | ---------------------------- |
| Theme data, file storage     | `{path}/themes`            | `{path}/acme/themes`         |
| Theme data, database storage | rows with an empty `scope` | rows with `scope = 'acme'`   |
| Cached theme settings        | `theme_settings.*`         | `acme:theme_settings.*`      |
| Uploaded images and videos   | `bagisto-visual/images`    | `acme/bagisto-visual/images` |

Theme code, theme configuration and Bagisto's own data are not scoped.

## The Full Page Cache

Bagisto's full page cache (Spatie's response cache) stores pages by host and URL. If tenants share a host, a page cached for one tenant is served to the others. Visual does not change this for you.

To keep cached pages apart, add the scope to the cache name with a cache profile of your own:

```php
namespace App\Http\CacheProfiles;

use BagistoPlus\Visual\Facades\Visual;
use Illuminate\Http\Request;
use Webkul\FPC\CacheProfiles\FullPageCacheProfile;

class ScopedFullPageCacheProfile extends FullPageCacheProfile
{
    public function useCacheNameSuffix(Request $request): string
    {
        return Visual::storageScope().'|'.parent::useCacheNameSuffix($request);
    }
}
```

Then set it as `cache_profile` in `config/responsecache.php`. The scope must already be resolvable when the response cache middleware runs.

## Jobs and Commands

Outside a request, such as in a queued job or a console command, run Visual's work under a given scope with `withStorageScope`. It puts your resolver back afterwards:

```php
Visual::withStorageScope('acme', function () {
    // Reads and writes here use the acme scope.
});
```

The import and export commands take a `--scope` option. See [Database Storage](./database-storage.md#import-and-export).

## Adding Scopes to an Existing Install

Data saved before you set a resolver has no scope. Once the resolver returns a scope, Visual no longer sees that data, and every scope starts from the theme's defaults. To give it to a scope, move it there:

- **File storage**: move `{path}/themes` to `{path}/{scope}/themes`.
- **Database storage**: set the `scope` column of the `visual_theme_documents`, `visual_theme_versions` and `visual_theme_states` rows from an empty string to the scope.
- **Uploads**: leave them where they are. Theme data refers to an upload by its full path on the disk, so the moved theme data still shows them. They are no longer listed in the scope's media library, though. To list them there, copy them to `{scope}/bagisto-visual/images` and `{scope}/bagisto-visual/videos` and keep the originals.

Then clear the application cache with `php artisan cache:clear`.

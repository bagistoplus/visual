<?php

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Persistence\DatabaseThemeDataStore;
use Illuminate\Cache\Events\RetrievingKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    config()->set('bagisto_visual.storage', 'database');
    migrateThemeDataTables();

    $this->store = app(DatabaseThemeDataStore::class);
});

it('is the theme data store in database mode', function () {
    expect(app(ThemeDataStore::class))->toBe($this->store);
});

it('reads theme data from visual paths, with a placeholder for no scope', function () {
    expect($this->store->themesPath(''))->toBe('visual://_/themes')
        ->and($this->store->themesPath('acme'))->toBe('visual://acme/themes')
        ->and($this->store->scopeFromSegment('_'))->toBe('')
        ->and($this->store->scopeFromSegment('acme'))->toBe('acme');
});

it('stores an editor document and marks the theme as edited', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');

    expect($this->store->index('', 'demo', 'editor'))->toHaveKey('default/en/theme.json')
        ->and($this->store->index('', 'demo', 'editor')['default/en/theme.json']['size'])->toBe(7)
        ->and($this->store->contents('', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"a":1}')
        ->and($this->store->hasUnpublishedEdits('demo'))->toBeTrue();
});

it('replaces a document it already holds', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');
    $this->store->put('demo', 'default/en/theme.json', '{"a":2}');

    expect($this->store->contents('', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"a":2}')
        ->and(DB::table('visual_theme_documents')->count())->toBe(1);
});

it('deletes a document and marks the theme as edited', function () {
    $this->store->put('demo', 'default/en/theme.json', '{}');
    $this->store->publish('demo', []);

    $this->store->delete('demo', 'default/en/theme.json');

    expect($this->store->index('', 'demo', 'editor'))->toBe([])
        ->and($this->store->hasUnpublishedEdits('demo'))->toBeTrue();
});

it('publishes the given documents as the live version and clears the edits', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"parent":"x","a":1}');

    $this->store->publish('demo', ['default/en/theme.json' => '{"a":1}']);

    expect($this->store->contents('', 'demo', 'live', 'default/en/theme.json'))->toBe('{"a":1}')
        ->and($this->store->contents('', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"parent":"x","a":1}')
        ->and($this->store->hasUnpublishedEdits('demo'))->toBeFalse();
});

it('serves only what the newest publish holds', function () {
    $this->store->publish('demo', ['default/en/templates/old.json' => '{}']);
    $this->store->publish('demo', ['default/en/theme.json' => '{}']);

    expect(array_keys($this->store->index('', 'demo', 'live')))->toBe(['default/en/theme.json']);
});

it('stamps each publish later than the one before, even within a second', function () {
    $this->store->publish('demo', ['default/en/theme.json' => '{"a":1}']);
    $first = $this->store->index('', 'demo', 'live')['default/en/theme.json']['stamp'];

    $this->store->publish('demo', ['default/en/theme.json' => '{"a":2}']);
    $second = $this->store->index('', 'demo', 'live')['default/en/theme.json']['stamp'];

    expect($second)->toBeGreaterThan($first);
});

it('keeps the configured number of versions and never drops the live one', function (int $keep, int $expected) {
    config()->set('bagisto_visual.versions_to_keep', $keep);

    foreach (range(1, 4) as $i) {
        $this->store->publish('demo', ['default/en/theme.json' => "{\"v\":{$i}}"]);
    }

    $liveId = DB::table('visual_theme_states')->value('live_version_id');

    expect(DB::table('visual_theme_versions')->count())->toBe($expected)
        ->and(DB::table('visual_theme_versions')->where('id', $liveId)->exists())->toBeTrue()
        ->and($this->store->contents('', 'demo', 'live', 'default/en/theme.json'))->toBe('{"v":4}');
})->with([
    'two' => [2, 2],
    'none still keeps the live one' => [0, 1],
]);

it('discards editor documents in favour of what is published', function () {
    $this->store->publish('demo', ['default/en/theme.json' => '{"a":1}']);
    $this->store->put('demo', 'default/en/theme.json', '{"a":2}');
    $this->store->put('demo', 'default/fr/theme.json', '{"a":3}');

    $this->store->discard('demo');

    expect(array_keys($this->store->index('', 'demo', 'editor')))->toBe(['default/en/theme.json'])
        ->and($this->store->contents('', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"a":1}')
        ->and($this->store->hasUnpublishedEdits('demo'))->toBeFalse();
});

it('discards to nothing when nothing was ever published', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"a":2}');

    $this->store->discard('demo');

    expect($this->store->index('', 'demo', 'editor'))->toBe([]);
});

it('lets another instance see a write through the shared cache', function () {
    $other = new DatabaseThemeDataStore;
    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');
    expect($other->contents('', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"a":1}');

    $this->store->put('demo', 'default/en/theme.json', '{"a":2}');
    $other->flush();

    expect($other->contents('', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"a":2}');
});

it('reads through to the database when the cache has lost everything', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');
    $this->store->publish('demo', ['default/en/theme.json' => '{"a":1}']);

    Cache::flush();
    $this->store->flush();

    expect($this->store->contents('', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"a":1}')
        ->and($this->store->contents('', 'demo', 'live', 'default/en/theme.json'))->toBe('{"a":1}');
});

it('keeps scopes apart', function () {
    $tenant = 'acme';
    Visual::resolveStorageScopeUsing(function () use (&$tenant) {
        return $tenant;
    });

    $this->store->put('demo', 'default/en/theme.json', '{"tenant":"acme"}');
    $this->store->publish('demo', ['default/en/theme.json' => '{"tenant":"acme"}']);

    $tenant = 'globex';
    $this->store->put('demo', 'default/en/theme.json', '{"tenant":"globex"}');

    expect($this->store->contents('acme', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"tenant":"acme"}')
        ->and($this->store->contents('globex', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"tenant":"globex"}')
        ->and($this->store->index('globex', 'demo', 'live'))->toBe([])
        ->and($this->store->hasUnpublishedEdits('demo'))->toBeTrue();

    $tenant = 'acme';

    expect($this->store->hasUnpublishedEdits('demo'))->toBeFalse();
});

it('clears every scope', function () {
    $this->store->put('demo', 'default/en/theme.json', '{}');
    $this->store->publish('demo', ['default/en/theme.json' => '{}']);

    $this->store->clear();

    expect(DB::table('visual_theme_documents')->count())->toBe(0)
        ->and(DB::table('visual_theme_versions')->count())->toBe(0)
        ->and(DB::table('visual_theme_states')->count())->toBe(0);
});

it('lets another instance read a write without going to the database', function () {
    $this->store->index('', 'demo', 'editor');
    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');

    $other = new DatabaseThemeDataStore;

    expect(countThemeDataQueries(fn () => expect($other->contents('', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"a":1}')))->toBe(0);
});

it('lets another instance read a publish without going to the database', function () {
    $this->store->publish('demo', ['default/en/theme.json' => '{"a":1}']);

    $other = new DatabaseThemeDataStore;

    expect(countThemeDataQueries(fn () => expect($other->contents('', 'demo', 'live', 'default/en/theme.json'))->toBe('{"a":1}')))->toBe(0);
});

it('does not read a document again when another one changes', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');
    $this->store->put('demo', 'default/en/templates/index.json', '{"v":1}');

    $other = new DatabaseThemeDataStore;
    $other->contents('', 'demo', 'editor', 'default/en/theme.json');

    $this->store->put('demo', 'default/en/templates/index.json', '{"v":2}');
    $other->flush();

    expect(countThemeDataQueries(fn () => expect($other->contents('', 'demo', 'editor', 'default/en/theme.json'))->toBe('{"a":1}')))->toBe(0)
        ->and($other->contents('', 'demo', 'editor', 'default/en/templates/index.json'))->toBe('{"v":2}');
});

it('counts one editor version per edit', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');
    $this->store->put('demo', 'default/en/theme.json', '{"a":2}');
    $this->store->delete('demo', 'default/en/theme.json');

    expect(DB::table('visual_theme_states')->value('editor_version'))->toBe(3);
});

it('does not carry its index forward when another instance wrote in between', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');
    $this->store->index('', 'demo', 'editor');

    (new DatabaseThemeDataStore)->put('demo', 'default/fr/theme.json', '{"b":1}');
    $this->store->put('demo', 'default/de/theme.json', '{"c":1}');

    expect(array_keys((new DatabaseThemeDataStore)->index('', 'demo', 'editor')))
        ->toEqualCanonicalizing(['default/en/theme.json', 'default/fr/theme.json', 'default/de/theme.json'])
        ->and(array_keys($this->store->index('', 'demo', 'editor')))
        ->toEqualCanonicalizing(['default/en/theme.json', 'default/fr/theme.json', 'default/de/theme.json']);
});

it('reads the documents of a folder in one cache call', function () {
    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');
    $this->store->put('demo', 'default/en/templates/index.json', '{"b":1}');
    $this->store->put('demo', 'default/en/regions/header.json', '{"c":1}');
    $this->store->put('demo', 'default/fr/theme.json', '{"d":1}');

    $other = new DatabaseThemeDataStore;
    $other->index('', 'demo', 'editor');
    $other->contents('', 'demo', 'editor', 'default/en/theme.json');

    $reads = 0;
    Event::listen(RetrievingKey::class, function () use (&$reads) {
        $reads++;
    });

    expect($other->contents('', 'demo', 'editor', 'default/en/templates/index.json'))->toBe('{"b":1}')
        ->and($other->contents('', 'demo', 'editor', 'default/en/regions/header.json'))->toBe('{"c":1}')
        ->and($reads)->toBe(0)
        ->and($other->contents('', 'demo', 'editor', 'default/fr/theme.json'))->toBe('{"d":1}')
        ->and($reads)->toBe(1);
});

it('keeps its rows and cache entries on the configured connection and cache store', function () {
    config()->set('database.connections.visual', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
    config()->set('cache.stores.visual', ['driver' => 'array']);
    config()->set('bagisto_visual.storages.database', ['connection' => 'visual', 'cache' => 'visual']);
    migrateThemeDataTables();

    $this->store->put('demo', 'default/en/theme.json', '{"a":1}');
    $this->store->publish('demo', ['default/en/theme.json' => '{"a":1}']);

    expect(DB::connection('visual')->table('visual_theme_documents')->count())->toBe(1)
        ->and(DB::connection('visual')->table('visual_theme_versions')->count())->toBe(1)
        ->and(DB::table('visual_theme_documents')->count())->toBe(0)
        ->and(DB::table('visual_theme_versions')->count())->toBe(0)
        ->and(Cache::store('visual')->get('visual:theme-data:_:demo:live'))->not->toBeNull()
        ->and(Cache::get('visual:theme-data:_:demo:live'))->toBeNull()
        ->and((new DatabaseThemeDataStore)->contents('', 'demo', 'live', 'default/en/theme.json'))->toBe('{"a":1}');
});

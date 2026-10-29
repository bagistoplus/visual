<?php

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Persistence\DeleteThemeCopy;
use BagistoPlus\Visual\Persistence\DuplicateTheme;
use BagistoPlus\Visual\Persistence\UpdateThemeCopy;
use BagistoPlus\Visual\Theme\ThemeCopies;
use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/visual-theme-copy-actions-'.uniqid();
    config()->set('bagisto_visual.storages.file.path', $this->dataPath);

    $this->store = app(ThemeDataStore::class);
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->dataPath);
});

it('copies the drafts and what is published of a theme to a new copy', function () {
    $this->store->publish('fake-theme', ['default/en/theme.json' => '{"live":1}']);
    $this->store->put('fake-theme', 'default/en/theme.json', '{"draft":1}');

    $code = app(DuplicateTheme::class)->handle('fake-theme', 'Staging', 'Winter sale');
    $snapshot = $this->store->snapshot($code);

    expect($code)->toMatch('/^fake-theme-[a-z0-9]{6}$/')
        ->and($snapshot->editor)->toBe(['default/en/theme.json' => '{"draft":1}'])
        ->and($snapshot->live)->toBe(['default/en/theme.json' => '{"live":1}'])
        ->and($snapshot->edited)->toBeTrue()
        ->and($snapshot->meta)->toMatchArray(['parent' => 'fake-theme', 'name' => 'Staging', 'description' => 'Winter sale'])
        ->and(app(ThemeCopies::class)->isCopy($code))->toBeTrue();
});

it('duplicates a theme that holds no data yet', function () {
    $code = app(DuplicateTheme::class)->handle('fake-theme', 'Staging');

    expect(app(ThemeCopies::class)->isCopy($code))->toBeTrue()
        ->and($this->store->snapshot($code)->live)->toBeNull();
});

it('makes a copy of a copy a child of the theme package', function () {
    $first = app(DuplicateTheme::class)->handle('fake-theme', 'First');
    app(ThemeCopies::class)->register();

    $second = app(DuplicateTheme::class)->handle($first, 'Second');

    expect($this->store->meta($second)['parent'])->toBe('fake-theme')
        ->and($second)->toStartWith('fake-theme-');
});

it('refuses to duplicate a theme that is not a visual theme', function () {
    config()->set('themes.shop.default', ['name' => 'Default']);

    app(DuplicateTheme::class)->handle('default', 'Copy');
})->throws(InvalidArgumentException::class);

it('renames a copy and changes its description', function () {
    $code = app(DuplicateTheme::class)->handle('fake-theme', 'Staging', 'Old');

    app(UpdateThemeCopy::class)->handle($code, 'Renamed', null);

    expect($this->store->meta($code))->toMatchArray(['parent' => 'fake-theme', 'name' => 'Renamed', 'description' => null])
        ->and(app(ThemeCopies::class)->all()[$code]['name'])->toBe('Renamed');
});

it('deletes a copy and everything held for it', function () {
    $code = app(DuplicateTheme::class)->handle('fake-theme', 'Staging');
    $this->store->publish($code, ['default/en/theme.json' => '{}']);

    app(DeleteThemeCopy::class)->handle($code);

    expect($this->store->themes())->not->toContain($code)
        ->and(app(ThemeCopies::class)->isCopy($code))->toBeFalse();
});

it('keeps a copy a channel is set to', function () {
    $code = app(DuplicateTheme::class)->handle('fake-theme', 'Staging');

    core()->setChannels([(object) ['code' => 'default', 'name' => 'Default Store', 'theme' => $code, 'locales' => collect()]]);

    expect(fn () => app(DeleteThemeCopy::class)->handle($code))
        ->toThrow(DomainException::class, 'Default Store');

    expect(app(ThemeCopies::class)->isCopy($code))->toBeTrue();
});

it('refuses to delete a theme that is not a copy', function () {
    app(DeleteThemeCopy::class)->handle('fake-theme');
})->throws(InvalidArgumentException::class);

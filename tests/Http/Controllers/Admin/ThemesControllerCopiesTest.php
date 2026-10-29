<?php

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Http\Controllers\Admin\ThemesController;
use BagistoPlus\Visual\Persistence\DeleteThemeCopy;
use BagistoPlus\Visual\Persistence\DuplicateTheme;
use BagistoPlus\Visual\Persistence\UpdateThemeCopy;
use BagistoPlus\Visual\Theme\ThemeCopies;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/visual-themes-controller-copies-'.uniqid();
    config()->set('bagisto_visual.storages.file.path', $this->dataPath);
    app(ThemeCopies::class)->forget();

    app()->instance('Webkul\Theme\ThemeCatalog', new class
    {
        public function all(): Collection
        {
            return collect();
        }
    });
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->dataPath);
});

function copyRequest(array $data): Request
{
    return Request::create('/', 'POST', $data);
}

function makeThemeCopy(string $name = 'Staging'): string
{
    $code = app(DuplicateTheme::class)->handle('fake-theme', $name);
    app(ThemeCopies::class)->register();

    return $code;
}

it('duplicates a visual theme', function () {
    $response = app(ThemesController::class)->duplicate(copyRequest(['name' => 'Staging', 'description' => 'Winter']), 'fake-theme', app(DuplicateTheme::class));

    $copies = app(ThemeCopies::class)->all();

    expect($response->getTargetUrl())->toBe(route('visual.admin.themes.index'))
        ->and(session('success'))->toBe(trans('visual::admin.themes.copy-created'))
        ->and(array_values($copies))->toHaveCount(1)
        ->and(array_values($copies)[0])->toMatchArray(['parent' => 'fake-theme', 'name' => 'Staging', 'description' => 'Winter']);
});

it('asks a copy for a name', function () {
    app(ThemesController::class)->duplicate(copyRequest(['name' => '']), 'fake-theme', app(DuplicateTheme::class));
})->throws(ValidationException::class);

it('finds no theme to duplicate that is not a visual theme', function () {
    config()->set('themes.shop.default', ['name' => 'Default']);

    app(ThemesController::class)->duplicate(copyRequest(['name' => 'Copy']), 'default', app(DuplicateTheme::class));
})->throws(NotFoundHttpException::class);

it('renames a copy', function () {
    $code = makeThemeCopy();

    app(ThemesController::class)->update(copyRequest(['name' => 'Renamed']), $code, app(UpdateThemeCopy::class));

    expect(app(ThemeCopies::class)->all()[$code]['name'])->toBe('Renamed')
        ->and(session('success'))->toBe(trans('visual::admin.themes.copy-updated'));
});

it('finds no copy to rename or delete in a theme package', function (string $method) {
    $controller = app(ThemesController::class);

    match ($method) {
        'update' => $controller->update(copyRequest(['name' => 'Renamed']), 'fake-theme', app(UpdateThemeCopy::class)),
        'destroy' => $controller->destroy('fake-theme', app(DeleteThemeCopy::class)),
    };
})->with(['update', 'destroy'])->throws(NotFoundHttpException::class);

it('deletes a copy', function () {
    $code = makeThemeCopy();

    app(ThemesController::class)->destroy($code, app(DeleteThemeCopy::class));

    expect(app(ThemeCopies::class)->isCopy($code))->toBeFalse()
        ->and(session('success'))->toBe(trans('visual::admin.themes.copy-deleted'));
});

it('says which channels keep a copy from being deleted', function () {
    $code = makeThemeCopy();
    core()->setChannels([(object) ['code' => 'default', 'name' => 'Default Store', 'theme' => $code, 'locales' => collect()]]);

    app(ThemesController::class)->destroy($code, app(DeleteThemeCopy::class));

    expect(app(ThemeCopies::class)->isCopy($code))->toBeTrue()
        ->and(session('error'))->toContain('Default Store');
});

it('lists a copy with the theme it is based on', function () {
    fakeSectionSystem(available: false);
    $code = makeThemeCopy();

    $card = app(ThemesController::class)->index()->getData()['themes']->firstWhere('code', $code);

    expect($card)->toMatchArray([
        'name' => 'Staging',
        'isCopy' => true,
        'parentName' => 'Fake Theme',
        'unavailable' => false,
    ]);
});

it('lists a copy whose theme is not installed as unavailable', function () {
    fakeSectionSystem(available: false);
    app(ThemeDataStore::class)->putMeta('gone-abc123', ['parent' => 'gone', 'name' => 'Orphan']);
    app(ThemeCopies::class)->forget();

    $card = app(ThemesController::class)->index()->getData()['themes']->firstWhere('code', 'gone-abc123');

    expect($card)->toMatchArray(['name' => 'Orphan', 'isCopy' => true, 'unavailable' => true]);
});

describe('the themes page', function () {
    function copiesPage(array $can, bool $unavailable = false): string
    {
        view()->addNamespace('admin', dirname(__DIR__, 3).'/views/admin');

        // The editor and preview links are covered elsewhere and need the shop's routes.
        app()->instance('Webkul\User\Bouncer', new class
        {
            public function hasPermission(string $permission): bool
            {
                return false;
            }
        });

        return view('visual::admin.themes.index', [
            'themes' => collect([[
                'code' => 'fake-theme-abc123',
                'name' => 'Staging',
                'screenshot' => 'fake.png',
                'author' => 'Bagisto Plus',
                'version' => '2.0.0',
                'description' => 'Winter',
                'documentationUrl' => null,
                'isVisual' => true,
                'isCopy' => true,
                'parentName' => 'Fake Theme',
                'unavailable' => $unavailable,
            ]]),
            'can' => $can,
        ])->render();
    }

    it('offers to duplicate, edit and delete a copy to who may', function () {
        $page = copiesPage(['duplicate' => true, 'edit' => true, 'delete' => true]);

        expect($page)->toContain(route('visual.admin.themes.duplicate', ['theme' => 'fake-theme-abc123']))
            ->and($page)->toContain(route('visual.admin.themes.update', ['theme' => 'fake-theme-abc123']))
            ->and($page)->toContain(route('visual.admin.themes.delete', ['theme' => 'fake-theme-abc123']))
            ->and($page)->toContain('Based on Fake Theme')
            ->and($page)->toContain('value="Staging (copy)"')
            ->and($page)->not->toContain('Bagisto Plus')
            ->and($page)->not->toContain('v2.0.0');
    });

    it('offers none of them to who may not', function () {
        $page = copiesPage(['duplicate' => false, 'edit' => false, 'delete' => false]);

        expect($page)->not->toContain('visual/themes/fake-theme-abc123');
    });

    it('offers only to delete an unavailable copy', function () {
        $page = copiesPage(['duplicate' => true, 'edit' => true, 'delete' => true], unavailable: true);

        expect($page)->toContain(route('visual.admin.themes.delete', ['theme' => 'fake-theme-abc123']))
            ->and($page)->not->toContain(route('visual.admin.themes.duplicate', ['theme' => 'fake-theme-abc123']))
            ->and($page)->not->toContain('visual/editor/fake-theme-abc123')
            ->and($page)->toContain(trans('visual::admin.themes.copy-unavailable'));
    });
});

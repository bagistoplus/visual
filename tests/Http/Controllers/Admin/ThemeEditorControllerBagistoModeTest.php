<?php

use BagistoPlus\Visual\Http\Controllers\Admin\ThemeEditorController;
use BagistoPlus\Visual\Persistence\DiscardTheme;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\PropertyBag;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

function callEditorMethod(string $method, array $arguments = [], array $dependencies = []): mixed
{
    $class = new ReflectionClass(ThemeEditorController::class);
    $controller = $class->newInstanceWithoutConstructor();

    foreach ($dependencies as $name => $dependency) {
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($controller, $dependency);
    }

    $reflected = new ReflectionMethod(ThemeEditorController::class, $method);
    $reflected->setAccessible(true);

    return $reflected->invoke($controller, ...$arguments);
}

beforeEach(function () {
    config()->set('themes.shop', [
        'default' => ['name' => 'Default'],
        'visual-demo' => ['code' => 'visual-demo', 'name' => 'Visual Demo', 'visual_theme' => true],
    ]);
});

it('opens on every theme it has a way of reading', function () {
    fakeSectionSystem(available: true);

    expect(callEditorMethod('getEditableThemes'))->toBe(['default', 'visual-demo']);
});

it('opens on visual themes alone when bagisto declares no section types', function () {
    fakeSectionSystem(available: false);

    expect(callEditorMethod('getEditableThemes'))->toBe(['visual-demo']);
});

it('refuses to open the editor on a theme it has no way of editing', function () {
    fakeSectionSystem(available: false);

    callEditorMethod('index', ['default']);
})->throws(NotFoundHttpException::class);

it('resolves a theme whose config does not spell out its own code', function () {
    fakeSectionSystem(available: true);

    $theme = callEditorMethod('resolveTheme', ['default']);

    expect($theme->code)->toBe('default')
        ->and($theme->name)->toBe('Default')
        ->and($theme->editorMode()->isBagisto())->toBeTrue();
});

it('refuses a theme that is not registered', function () {
    fakeSectionSystem(available: true);

    callEditorMethod('resolveTheme', ['nope']);
})->throws(NotFoundHttpException::class);

it('tells the editor which mode a theme is edited in', function () {
    fakeSectionSystem(available: true);

    $dependencies = ['themeSettingsLoader' => new class extends ThemeSettingsLoader
    {
        public function __construct() {}

        public function loadThemeSettings($theme): PropertyBag
        {
            return new PropertyBag;
        }
    }];

    expect(callEditorMethod('loadTheme', ['default'], $dependencies)['editorMode'])->toBe('bagisto')
        ->and(callEditorMethod('loadTheme', ['visual-demo'], $dependencies)['editorMode'])->toBe('visual');
});

it('drops the drafts of a theme whose sections bagisto stores', function () {
    fakeSectionSystem(available: true);

    $store = new class extends SectionStore
    {
        public array $discarded = [];

        public function __construct() {}

        public function discard(int $channelId, string $themeCode): void
        {
            $this->discarded = [$channelId, $themeCode];
        }
    };

    $files = new class extends DiscardTheme
    {
        public array $discarded = [];

        public function __construct() {}

        public function handle(string $themeCode): void
        {
            $this->discarded[] = $themeCode;
        }
    };

    $response = callEditorMethod(
        'discardTheme',
        [Request::create('/', 'POST', ['theme' => 'default'])],
        ['sectionStore' => $store, 'discardTheme' => $files],
    );

    expect($store->discarded)->toBe([1, 'default'])
        ->and($files->discarded)->toBe(['default'])
        ->and($response->getData(true)['success'])->toBeTrue();
});

it('leaves a visual theme no way to discard, its edits being held elsewhere', function () {
    fakeSectionSystem(available: true);

    callEditorMethod('discardTheme', [Request::create('/', 'POST', ['theme' => 'visual-demo'])]);
})->throws(NotFoundHttpException::class);

function mediaStore(?string $theme, array $media = ['path' => 'themes/default/sections/7/a.png', 'type' => 'image']): SectionStore
{
    return new class($theme, $media) extends SectionStore
    {
        public array $stored = [];

        public function __construct(protected ?string $theme, protected array $media) {}

        public function themeOf(int $rowId): ?string
        {
            return $this->theme;
        }

        public function storeMedia(int $rowId, UploadedFile $file): array
        {
            $this->stored = [$rowId, $file->getClientOriginalName()];

            return $this->media + ['url' => '/storage/'.$this->media['path']];
        }
    };
}

function mediaRequest(string $theme, int $section): Request
{
    return Request::create('/', 'POST', ['theme' => $theme, 'section' => $section], [], [
        'file' => new UploadedFile(__DIR__.'/../../../Fixtures/media/pixel.png', 'pixel.png', 'image/png', null, true),
    ]);
}

it('keeps a file among the media of the section it was added to', function () {
    fakeSectionSystem(available: true);

    $store = mediaStore('default');

    $response = callEditorMethod('uploadSectionMedia', [mediaRequest('default', 7)], ['sectionStore' => $store]);

    expect($store->stored)->toBe([7, 'pixel.png'])
        ->and($response->getData(true)['url'])->toBe('/storage/themes/default/sections/7/a.png');
});

it('refuses a file for a section stored under another theme', function () {
    fakeSectionSystem(available: true);

    callEditorMethod('uploadSectionMedia', [mediaRequest('default', 7)], ['sectionStore' => mediaStore('other')]);
})->throws(NotFoundHttpException::class);

it('refuses a file for a theme holding its edits in files', function () {
    fakeSectionSystem(available: true);

    callEditorMethod('uploadSectionMedia', [mediaRequest('visual-demo', 7)], ['sectionStore' => mediaStore('visual-demo')]);
})->throws(NotFoundHttpException::class);

function libraryStore(?string $theme, array $media = [], ?array $copy = null): SectionStore
{
    return new class($theme, $media, $copy) extends SectionStore
    {
        public array $copied = [];

        public function __construct(protected ?string $theme, protected array $media, protected ?array $copy) {}

        public function themeOf(int $rowId): ?string
        {
            return $this->theme;
        }

        public function media(string $themeCode): array
        {
            return $this->media;
        }

        public function copyMedia(int $rowId, string $path, string $themeCode): ?array
        {
            $this->copied = [$rowId, $path, $themeCode];

            return $this->copy;
        }
    };
}

it('lists every file the theme sections hold', function () {
    fakeSectionSystem(available: true);

    $files = [['path' => 'themes/default/sections/7/a.png', 'url' => '/storage/a.png', 'section' => 7]];

    $response = callEditorMethod(
        'listSectionMedia',
        [Request::create('/', 'GET', ['theme' => 'default'])],
        ['sectionStore' => libraryStore('default', $files)],
    );

    expect($response->getData(true))->toBe($files);
});

it('lists nothing for a theme holding its edits in files', function () {
    fakeSectionSystem(available: true);

    callEditorMethod('listSectionMedia', [Request::create('/', 'GET', ['theme' => 'visual-demo'])]);
})->throws(NotFoundHttpException::class);

it('copies a file into the section it is to be drawn by', function () {
    fakeSectionSystem(available: true);

    $copy = ['path' => 'themes/default/sections/9/b.png', 'url' => '/storage/b.png'];
    $store = libraryStore('default', [], $copy);

    $response = callEditorMethod(
        'copySectionMedia',
        [Request::create('/', 'POST', [
            'theme' => 'default',
            'section' => 9,
            'path' => 'themes/default/sections/7/a.png',
        ])],
        ['sectionStore' => $store],
    );

    expect($store->copied)->toBe([9, 'themes/default/sections/7/a.png', 'default'])
        ->and($response->getData(true))->toBe($copy);
});

it('refuses to copy a file the theme does not hold', function () {
    fakeSectionSystem(available: true);

    callEditorMethod(
        'copySectionMedia',
        [Request::create('/', 'POST', [
            'theme' => 'default',
            'section' => 9,
            'path' => '../../../.env',
        ])],
        ['sectionStore' => libraryStore('default', [], null)],
    );
})->throws(NotFoundHttpException::class);

it('refuses to copy into a section stored under another theme', function () {
    fakeSectionSystem(available: true);

    callEditorMethod(
        'copySectionMedia',
        [Request::create('/', 'POST', [
            'theme' => 'default',
            'section' => 9,
            'path' => 'themes/default/sections/7/a.png',
        ])],
        ['sectionStore' => libraryStore('other', [], ['path' => 'x', 'url' => 'x'])],
    );
})->throws(NotFoundHttpException::class);

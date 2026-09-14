<?php

use BagistoPlus\Visual\Http\Controllers\Admin\ThemeEditorController;
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

    $response = callEditorMethod(
        'discardTheme',
        [Request::create('/', 'POST', ['theme' => 'default'])],
        ['sectionStore' => $store],
    );

    expect($store->discarded)->toBe([1, 'default'])
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

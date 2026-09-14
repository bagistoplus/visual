<?php

use BagistoPlus\Visual\Http\Controllers\Admin\ThemeEditorController;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\PropertyBag;
use Illuminate\Http\Request;
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

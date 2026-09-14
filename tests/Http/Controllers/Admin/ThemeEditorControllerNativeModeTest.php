<?php

use BagistoPlus\Visual\Http\Controllers\Admin\ThemeEditorController;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\PropertyBag;
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
        ->and($theme->editorMode()->isNative())->toBeTrue();
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

    expect(callEditorMethod('loadTheme', ['default'], $dependencies)['editorMode'])->toBe('native')
        ->and(callEditorMethod('loadTheme', ['visual-demo'], $dependencies)['editorMode'])->toBe('visual');
});

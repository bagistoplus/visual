<?php

use BagistoPlus\Visual\Blocks\SimpleBlock;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Middlewares\InjectThemeEditorScript;
use BagistoPlus\Visual\Middlewares\RegisterVisualSchemas;
use BagistoPlus\Visual\Middlewares\UseShopThemeFromRequest;
use BagistoPlus\Visual\Providers\CoreServiceProvider;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Support\EditorTranslationReferenceCollector;
use BagistoPlus\Visual\TemplateRegistrar;
use Craftile\Laravel\BlockSchemaRegistry;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Relations\Relation;

class CoreServiceProviderTranslationReferenceBlock extends SimpleBlock
{
    protected static string $type = 'core-service-provider-translation-reference-block';

    public static function settings(): array
    {
        return [
            Text::make('title', 'Title'),
        ];
    }
}

it('does not register storefront middlewares on the application http kernel', function () {
    $kernel = app(Kernel::class);
    $middleware = (new ReflectionProperty($kernel, 'middleware'))->getValue($kernel);

    expect($middleware)
        ->not->toContain(RegisterVisualSchemas::class)
        ->not->toContain(InjectThemeEditorScript::class);
});

it('registers visual storefront middlewares on the shop middleware group', function () {
    $middlewareGroups = app('router')->getMiddlewareGroups();
    $shopMiddleware = $middlewareGroups['shop'] ?? [];

    expect($shopMiddleware)
        ->toContain(UseShopThemeFromRequest::class)
        ->toContain(RegisterVisualSchemas::class)
        ->toContain(InjectThemeEditorScript::class)
        ->and(array_search(UseShopThemeFromRequest::class, $shopMiddleware, true))
        ->toBeLessThan(array_search(RegisterVisualSchemas::class, $shopMiddleware, true))
        ->and(array_search(RegisterVisualSchemas::class, $shopMiddleware, true))
        ->toBeLessThan(array_search(InjectThemeEditorScript::class, $shopMiddleware, true));
});

it('registers discovered schemas after the app boots in console', function () {
    Craftile::spy();

    $provider = new CoreServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'bootConsoleSchemaRegistration');
    $method->setAccessible(true);
    $method->invoke($provider);

    Craftile::shouldHaveReceived('registerDiscoveredSchemas')
        ->with(Mockery::on(fn ($filter) => is_callable($filter) && $filter([], 'block') === true))
        ->once();
});

it('registers editor templates during active visual requests', function () {
    ThemeEditor::shouldReceive('active')->andReturnTrue();

    $registrar = Mockery::mock(TemplateRegistrar::class);
    $registrar->shouldReceive('registerTemplates')->once();
    $this->app->instance(TemplateRegistrar::class, $registrar);

    $provider = new CoreServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'bootTemplates');
    $method->setAccessible(true);
    $method->invoke($provider);
});

it('does not collect editor translation references outside design mode', function () {
    app(BlockSchemaRegistry::class)->register(BlockSchema::fromClass(CoreServiceProviderTranslationReferenceBlock::class));

    $collector = app(EditorTranslationReferenceCollector::class);
    $collector->reset();

    ThemeEditor::shouldReceive('inDesignMode')->andReturnFalse();

    $provider = new CoreServiceProvider($this->app);
    $method = new ReflectionMethod($provider, 'bootCraftile');
    $method->setAccessible(true);
    $method->invoke($provider);

    Craftile::createBlockData([
        'id' => 'hero',
        'type' => 'core-service-provider-translation-reference-block',
        'properties' => [
            'title' => 't:block.title',
        ],
    ]);

    expect($collector->forBlockIds(['hero']))->toBe([]);
});

it('maps the theme morph key to the theme section model that exists', function () {
    $section = 'Webkul\Theme\Models\Section';
    $expected = class_exists($section) ? $section : 'Webkul\Theme\Models\ThemeCustomization';

    expect(Relation::getMorphedModel('theme'))->toBe($expected)
        ->and(class_exists(Relation::getMorphedModel('theme')))->toBeTrue();
});

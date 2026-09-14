<?php

use BagistoPlus\Visual\Data\SectionRecord;
use BagistoPlus\Visual\Middlewares\InjectThemeEditorScript;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Support\EditorInheritanceMetadata;
use BagistoPlus\Visual\Support\SectionPageBuilder;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\ThemeEditor;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\PreviewDataCollector;
use Illuminate\Support\Collection;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Theme\Facades\Themes as ThemesFacade;

class PageDataReadingMiddleware extends InjectThemeEditorScript
{
    public function pageData(): array
    {
        return $this->getCurrentPageData();
    }
}

function currentThemeIs(Theme $theme): void
{
    ThemesFacade::swap(new class($theme)
    {
        public function __construct(protected Theme $theme) {}

        public function current(): Theme
        {
            return $this->theme;
        }
    });
}

function storeHolding(array $records): SectionStore
{
    return new class($records) extends SectionStore
    {
        public function __construct(protected array $records) {}

        public function records(int $channelId, string $themeCode, string $locale): Collection
        {
            return collect($this->records);
        }
    };
}

function middlewareReading(SectionStore $store): PageDataReadingMiddleware
{
    return new PageDataReadingMiddleware(
        Mockery::mock(ThemeEditor::class),
        Mockery::mock(ThemeSettingsLoader::class),
        Mockery::mock(CategoryRepository::class),
        Mockery::mock(ProductRepository::class),
        Mockery::mock(PreviewDataCollector::class),
        app(EditorInheritanceMetadata::class),
        $store,
        app(SectionPageBuilder::class),
    );
}

it('reads a native theme\'s page from the rows its sections are stored as', function () {
    offerTypes(['image_carousel' => carouselType()]);
    currentThemeIs(Theme::make(['code' => 'default', 'name' => 'Default']));

    $page = middlewareReading(storeHolding([
        new SectionRecord(7, 'image_carousel', 'Hero', true, 1, [
            'title' => 'Summer',
            'images' => [['image' => 'a.webp', 'link' => '/one']],
        ]),
    ]))->pageData();

    expect($page['blocks']['section-7']['properties'])->toBe(['title' => 'Summer'])
        ->and($page['blocks']['section-7']['children'])->toBe(['section-7.images.0'])
        ->and($page['regions'][0])->toBe([
            'name' => 'content',
            'shared' => false,
            'blocks' => ['section-7'],
        ]);
});

it('leaves a visual theme to report the blocks it rendered', function () {
    offerTypes(['image_carousel' => carouselType()]);
    currentThemeIs(Theme::make(['code' => 'debut', 'name' => 'Debut', 'visual_theme' => true]));

    $collector = Mockery::mock(PreviewDataCollector::class);
    $collector->shouldReceive('getCollectedData')->once()->andReturn(['blocks' => [], 'regions' => []]);

    $middleware = new PageDataReadingMiddleware(
        Mockery::mock(ThemeEditor::class),
        Mockery::mock(ThemeSettingsLoader::class),
        Mockery::mock(CategoryRepository::class),
        Mockery::mock(ProductRepository::class),
        $collector,
        app(EditorInheritanceMetadata::class),
        storeHolding([new SectionRecord(7, 'image_carousel', 'Hero', true, 1, [])]),
        app(SectionPageBuilder::class),
    );

    expect($middleware->pageData())->toBe(['blocks' => [], 'regions' => []]);
});

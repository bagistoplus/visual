<?php

use BagistoPlus\Visual\Blocks\SimpleBlock;
use BagistoPlus\Visual\LivewireFeatures\BlockDataSynth;
use BagistoPlus\Visual\Persistence\DatabaseThemeDataStore;
use BagistoPlus\Visual\Persistence\EditorDataStore;
use BagistoPlus\Visual\Persistence\PublishTheme;
use BagistoPlus\Visual\Persistence\VisualStreamWrapper;
use BagistoPlus\Visual\Providers\CoreServiceProvider;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\View\VisualThemeViewFinder;
use Craftile\Laravel\Facades\BlockDatastore;
use Craftile\Laravel\Facades\Craftile;
use Craftile\Laravel\View\JsonViewParser;
use Illuminate\Support\Facades\File;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Webkul\Theme\ThemeViewFinder;

class DatabaseStorageReadingBlock extends SimpleBlock
{
    protected static string $type = 'database-storage-reading-block';

    protected static string $view = 'database-storage-reading::stream-block';

    public static function settings(): array
    {
        return [
            Text::make('title', 'Title'),
        ];
    }
}

function streamTemplate(string $title): string
{
    return json_encode([
        'blocks' => [
            'hero' => ['id' => 'hero', 'type' => 'database-storage-reading-block', 'properties' => ['title' => $title], 'children' => ['hero-child']],
            'hero-child' => ['id' => 'hero-child', 'type' => 'database-storage-reading-block', 'properties' => ['title' => "{$title} child"], 'parentId' => 'hero', 'children' => []],
        ],
        'regions' => [['name' => 'main', 'blocks' => ['hero']]],
    ]);
}

function startStreamRequest(): void
{
    app(JsonViewParser::class)->clearCache();
    BlockDatastore::clear();
    view()->getFinder()->flush();
    app(DatabaseThemeDataStore::class)->flush();
}

beforeEach(function () {
    config()->set('bagisto_visual.storage', 'database');
    migrateThemeDataTables();

    $this->store = app(DatabaseThemeDataStore::class);
    VisualStreamWrapper::register($this->store);

    view()->addNamespace('database-storage-reading', dirname(__DIR__).'/views');
    Craftile::registerBlocks([DatabaseStorageReadingBlock::class]);
    Craftile::detectPreviewUsing(fn () => false);
    startStreamRequest();
});

it('renders a published template through the view finder', function () {
    $this->store->publish('demo', ['default/en/templates/index.json' => streamTemplate('Hello')]);
    view()->addNamespace('theme-data', 'visual://_/themes/demo/live/default/en');

    $html = view('theme-data::templates.index')->render();

    expect($html)->toContain('<span>Hello</span>')
        ->and($html)->toContain('<span>Hello child</span>');
});

it('renders what the newest publish holds', function () {
    view()->addNamespace('theme-data', 'visual://_/themes/demo/live/default/en');

    $this->store->publish('demo', ['default/en/templates/index.json' => streamTemplate('Before')]);
    $before = view('theme-data::templates.index')->render();

    $this->store->publish('demo', ['default/en/templates/index.json' => streamTemplate('After')]);
    startStreamRequest();
    $after = view('theme-data::templates.index')->render();

    expect($before)->toContain('Before')
        ->and($after)->toContain('<span>After</span>')
        ->and($after)->not->toContain('Before');
});

it('answers file and directory checks the way a filesystem would', function () {
    $this->store->put('demo', 'default/en/templates/index.json', '{}');
    $this->store->put('demo', 'default/en/templates/product/sale.json', '{}');
    $base = 'visual://_/themes/demo/editor';

    $listed = collect(File::allFiles("{$base}/default/en/templates"))
        ->map(fn ($file) => str_replace('\\', '/', $file->getRelativePathname()))
        ->sort()
        ->values()
        ->all();

    expect(File::isDirectory($base))->toBeTrue()
        ->and(File::isDirectory("{$base}/default/en/templates"))->toBeTrue()
        ->and(File::isFile("{$base}/default/en/templates/index.json"))->toBeTrue()
        ->and(File::exists("{$base}/default/fr/templates/index.json"))->toBeFalse()
        ->and(File::isDirectory('visual://_/themes/other/editor'))->toBeFalse()
        ->and($listed)->toBe(['index.json', 'product/sale.json']);
});

it('refuses writes, which belong to the store', function () {
    expect(@file_put_contents('visual://_/themes/demo/editor/default/en/theme.json', '{}'))->toBeFalse()
        ->and($this->store->index('', 'demo', 'editor'))->toBe([]);
});

it('saves and resolves inherited editor data through the editor data store', function () {
    $editorData = app(EditorDataStore::class);

    $editorData->save('demo', 'default/en/theme.json', ['a' => 1, 'b' => ['c' => 2]], null);
    $editorData->save('demo', 'default/fr/theme.json', ['b' => ['c' => 3]], 'default/en/theme.json');

    expect($editorData->loadResolved('demo', 'default/fr/theme.json'))->toBe(['a' => 1, 'b' => ['c' => 3]])
        ->and($editorData->relativePathFromAbsolute('demo', 'visual://_/themes/demo/editor/default/fr/theme.json'))->toBe('default/fr/theme.json');
});

it('publishes editor data resolved against its parents', function () {
    $editorData = app(EditorDataStore::class);
    $editorData->save('demo', 'default/en/theme.json', ['a' => 1, 'b' => 2], null);
    $editorData->save('demo', 'default/fr/theme.json', ['b' => 3], 'default/en/theme.json');

    app(PublishTheme::class)->handle('demo');

    expect(json_decode($this->store->contents('', 'demo', 'live', 'default/fr/theme.json'), true))->toBe(['a' => 1, 'b' => 3])
        ->and($this->store->hasUnpublishedEdits('demo'))->toBeFalse();
});

it('round trips a livewire block whose source is a visual path', function () {
    $this->store->publish('demo', ['default/en/templates/index.json' => streamTemplate('Livewire')]);
    $source = 'visual://_/themes/demo/live/default/en/templates/index.json';
    BlockDatastore::loadFile($source);

    $synth = new BlockDataSynth(Mockery::mock(ComponentContext::class), '');
    [$dehydrated] = $synth->dehydrate(BlockDatastore::getBlock('hero'));

    startStreamRequest();
    $hydrated = $synth->hydrate($dehydrated);

    expect($hydrated?->id)->toBe('hero')
        ->and($hydrated->getSourceFile())->toBe($source);
});

it('keeps visual paths whole in the bagisto view finder', function () {
    config()->set('app.admin_url', 'admin');
    $this->store->publish('demo', ['default/en/templates/index.json' => streamTemplate('Finder')]);

    $bagisto = new ThemeViewFinder(app('files'), ['visual://_/themes/demo/live/default/en'], ['json']);
    $bagisto->addNamespace('shop', dirname(__DIR__).'/views');
    $bagisto->replaceNamespace('errors', [dirname(__DIR__).'/views']);

    $finder = VisualThemeViewFinder::from($bagisto);

    expect($finder->find('shop::templates.index'))->toBe('visual://_/themes/demo/live/default/en/templates/index.json')
        ->and($finder->getHints()['errors'])->toContain('visual://_/themes/demo/live/default/en/errors')
        ->and(collect($finder->getHints()['errors'])->filter(fn ($path) => str_contains($path, 'visual://') && ! str_starts_with($path, 'visual://'))->all())->toBe([]);
});

it('keeps visual paths whole behind a windows base path', function () {
    $bagisto = new ThemeViewFinder(app('files'), [], ['json']);
    $bagisto->addNamespace('shop', ['C:\\app\\visual://_/themes/demo/live/default/en', dirname(__DIR__).'/views']);

    expect(VisualThemeViewFinder::from($bagisto)->getHints()['shop'])
        ->toBe(['visual://_/themes/demo/live/default/en', dirname(__DIR__).'/views']);
});

it('gives the view factory the visual finder even when the factory was made first', function () {
    $bagisto = new ThemeViewFinder(app('files'), [], ['json']);
    app()->instance('view.finder', $bagisto);
    view()->setFinder($bagisto);

    $provider = new CoreServiceProvider(app());
    (fn () => $this->bootDatabaseStorage())->call($provider);

    expect(app('view.finder'))->toBeInstanceOf(VisualThemeViewFinder::class)
        ->and(view()->getFinder())->toBe(app('view.finder'));
});

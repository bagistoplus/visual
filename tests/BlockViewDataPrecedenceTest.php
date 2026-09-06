<?php

use BagistoPlus\Visual\Blocks\BladeBlock;
use BagistoPlus\Visual\Blocks\LivewireBlock;
use BagistoPlus\Visual\Blocks\SimpleBlock;
use BagistoPlus\Visual\Blocks\SimpleSection;
use BagistoPlus\Visual\LivewireFeatures\SupportsBlockData;
use Craftile\Laravel\Facades\BlockDatastore as CraftileBlockDatastore;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;

class ViewDataPrecedenceSimpleBlock extends SimpleBlock
{
    protected static string $view = 'block-data-tests::probe';

    public static int $getViewDataCalls = 0;

    protected function getViewData(): array
    {
        self::$getViewDataCalls++;

        return ['probeValue' => 'FROM-BLOCK'];
    }
}

class ViewDataPrecedenceBladeBlock extends BladeBlock
{
    protected static string $view = 'block-data-tests::probe';

    public string $publicValue = 'FROM-PUBLIC-PROPERTY';

    protected function getViewData(): array
    {
        return [
            'probeValue' => 'FROM-BLOCK',
            'publicValue' => 'FROM-GET-VIEW-DATA',
            'block' => 'NOT-THE-BLOCK',
        ];
    }
}

class ViewDataPrecedenceSimpleSection extends SimpleSection
{
    protected static string $view = 'block-data-tests::probe';

    protected function getViewData(): array
    {
        return [
            'probeValue' => 'FROM-SECTION',
            'section' => 'NOT-THE-SECTION',
        ];
    }
}

class ViewDataPrecedenceLivewireBlock extends LivewireBlock
{
    protected static string $view = 'block-data-tests::probe';

    protected function getViewData(): array
    {
        return ['probeValue' => 'FROM-BLOCK'];
    }
}

beforeEach(function () {
    view()->addNamespace('block-data-tests', __DIR__.'/views');

    AliasLoader::getInstance()->alias(
        'BlockDatastore',
        CraftileBlockDatastore::class,
    );

    Craftile::registerBlocks([
        ViewDataPrecedenceSimpleBlock::class,
        ViewDataPrecedenceBladeBlock::class,
        ViewDataPrecedenceSimpleSection::class,
    ]);
});

function precedenceBlockData(string $type = 'view-data-precedence')
{
    return Craftile::createBlockData([
        'id' => 'block-1',
        'type' => $type,
        'properties' => [],
    ]);
}

it('uses simple block data over page context and evaluates it once', function () {
    ViewDataPrecedenceSimpleBlock::$getViewDataCalls = 0;

    $rendered = Blade::render(
        "@visualBlock('view-data-precedence-simple-block', 'simple-block-1', ['probeValue' => 'FROM-CONTEXT'])",
        deleteCachedView: true,
    );

    expect(trim($rendered))->toBe('FROM-BLOCK')
        ->and(ViewDataPrecedenceSimpleBlock::$getViewDataCalls)->toBe(1);
});

it('uses blade block data over context and keeps visual identity authoritative', function () {
    $blockData = precedenceBlockData();
    $block = new ViewDataPrecedenceBladeBlock($blockData, [
        'probeValue' => 'FROM-CONTEXT',
        'publicValue' => 'FROM-CONTEXT',
        'block' => 'FROM-CONTEXT',
    ]);

    $rendered = BladeCompiler::renderComponent($block);
    $data = $block->data();

    expect(trim($rendered))->toBe('FROM-BLOCK')
        ->and($data['probeValue'])->toBe('FROM-BLOCK')
        ->and($data['publicValue'])->toBe('FROM-GET-VIEW-DATA')
        ->and($data['block'])->toBe($blockData)
        ->and($data['__craftileContext']['probeValue'])->toBe('FROM-CONTEXT');
});

it('preserves simple section view data and keeps section identity authoritative', function () {
    $rendered = Blade::render(
        "@visualBlock('view-data-precedence-simple-section', 'simple-section-1', ['probeValue' => 'FROM-CONTEXT'])",
        deleteCachedView: true,
    );

    $sectionData = precedenceBlockData('view-data-precedence-section');
    $section = new ViewDataPrecedenceSimpleSection;
    $section->setBlockData($sectionData);
    $section->setContext(['probeValue' => 'FROM-CONTEXT']);

    $data = $section->data();

    expect(trim($rendered))->toBe('FROM-SECTION')
        ->and($data['probeValue'])->toBe('FROM-SECTION')
        ->and($data['section'])->toBe($sectionData);
});

it('uses livewire block data over page context', function () {
    $block = new ViewDataPrecedenceLivewireBlock;
    $block->setBlock(precedenceBlockData());
    $block->setContext(['probeValue' => 'FROM-CONTEXT']);

    $view = $block->render();
    $hook = new SupportsBlockData;
    $hook->setComponent($block);
    $hook->render($view);

    expect($view->getData()['probeValue'])->toBe('FROM-BLOCK')
        ->and($view->getData()['__craftileContext']['probeValue'])->toBe('FROM-CONTEXT');
});

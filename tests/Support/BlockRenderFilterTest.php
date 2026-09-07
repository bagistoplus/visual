<?php

use BagistoPlus\Visual\Data\BlockData;
use BagistoPlus\Visual\Support\BlockRenderFilter;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Http\Request;

it('loads the selective render set from the visual render query parameter', function () {
    session()->put('visual.render.render-key', ['selected']);
    app()->instance('request', Request::create('/?_visual_render=render-key'));

    $filter = new BlockRenderFilter;

    expect($filter->shouldRender(BlockData::make([
        'id' => 'selected',
        'type' => 'test',
    ])))->toBeTrue()
        ->and($filter->shouldRender(BlockData::make([
            'id' => 'not-selected',
            'type' => 'test',
        ])))->toBeFalse()
        ->and(session()->has('visual.render.render-key'))->toBeFalse();
});

it('does not support the removed vkey query parameter', function () {
    session()->put('visual.render.render-key', ['selected']);
    app()->instance('request', Request::create('/?_vkey=render-key'));

    $filter = new BlockRenderFilter;

    expect($filter->shouldRender(BlockData::make([
        'id' => 'not-selected',
        'type' => 'test',
    ])))->toBeTrue()
        ->and(session()->has('visual.render.render-key'))->toBeTrue();
});

it('collects only blocks from the render set during a partial render', function () {
    session()->put('visual.render.render-key', ['selected']);
    app()->instance('request', Request::create('/?_designMode=theme&_visual_render=render-key'));
    app()->forgetInstance(BlockRenderFilter::class);

    expect(Craftile::shouldCollectBlock(BlockData::make([
        'id' => 'selected',
        'type' => 'test',
    ])))->toBeTrue()
        ->and(Craftile::shouldCollectBlock(BlockData::make([
            'id' => 'not-selected',
            'type' => 'test',
            'disabled' => true,
        ])))->toBeFalse();
});

it('collects every block outside a partial render', function () {
    app()->instance('request', Request::create('/?_designMode=theme'));
    app()->forgetInstance(BlockRenderFilter::class);

    expect(Craftile::shouldCollectBlock(BlockData::make([
        'id' => 'any',
        'type' => 'test',
        'disabled' => true,
    ])))->toBeTrue();
});

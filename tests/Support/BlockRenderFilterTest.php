<?php

use BagistoPlus\Visual\Data\BlockData;
use BagistoPlus\Visual\Support\BlockRenderFilter;
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

it('renders static blocks under a rendered parent, with their subtree', function () {
    session()->put('visual.render.render-key', ['section']);
    app()->instance('request', Request::create('/?_visual_render=render-key'));

    $filter = new BlockRenderFilter;

    $section = BlockData::make(['id' => 'section', 'type' => 'test']);
    $zone = BlockData::make(['id' => 'zone', 'type' => 'test', 'static' => true, 'parentId' => 'section']);
    $zoneChild = BlockData::make(['id' => 'zone-child', 'type' => 'test', 'parentId' => 'zone']);
    $zoneGrandChild = BlockData::make(['id' => 'zone-grand-child', 'type' => 'test', 'parentId' => 'zone-child']);
    $dynamicSibling = BlockData::make(['id' => 'sibling', 'type' => 'test', 'parentId' => 'section']);

    expect($filter->shouldRender($section))->toBeTrue()
        ->and($filter->shouldRender($zone))->toBeTrue()
        ->and($filter->shouldRender($zoneChild))->toBeTrue()
        ->and($filter->shouldRender($zoneGrandChild))->toBeTrue()
        ->and($filter->shouldRender($dynamicSibling))->toBeFalse();
});

it('does not render static blocks whose parent was filtered out', function () {
    session()->put('visual.render.render-key', ['other']);
    app()->instance('request', Request::create('/?_visual_render=render-key'));

    $filter = new BlockRenderFilter;

    $section = BlockData::make(['id' => 'section', 'type' => 'test']);
    $zone = BlockData::make(['id' => 'zone', 'type' => 'test', 'static' => true, 'parentId' => 'section']);
    $zoneChild = BlockData::make(['id' => 'zone-child', 'type' => 'test', 'parentId' => 'zone']);

    expect($filter->shouldRender($section))->toBeFalse()
        ->and($filter->shouldRender($zone))->toBeFalse()
        ->and($filter->shouldRender($zoneChild))->toBeFalse();
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

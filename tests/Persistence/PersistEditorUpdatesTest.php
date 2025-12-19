<?php

use BagistoPlus\Visual\Persistence\PersistEditorUpdates;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('themes-data');
    $this->persistEditorUpdates = app(PersistEditorUpdates::class);
});

test('collectRegionBlocks collects root blocks', function () {
    $allBlocks = [
        'block-1' => ['id' => 'block-1', 'type' => 'text'],
        'block-2' => ['id' => 'block-2', 'type' => 'image'],
    ];

    $rootBlockIds = ['block-1'];

    $result = $this->persistEditorUpdates->collectRegionBlocks($allBlocks, $rootBlockIds);

    expect($result)->toHaveKey('block-1')
        ->and($result)->not->toHaveKey('block-2');
});

test('collectRegionBlocks collects nested children', function () {
    $allBlocks = [
        'block-1' => ['id' => 'block-1', 'type' => 'container', 'children' => ['block-2', 'block-3']],
        'block-2' => ['id' => 'block-2', 'type' => 'text'],
        'block-3' => ['id' => 'block-3', 'type' => 'image'],
        'block-4' => ['id' => 'block-4', 'type' => 'button'],
    ];

    $rootBlockIds = ['block-1'];

    $result = $this->persistEditorUpdates->collectRegionBlocks($allBlocks, $rootBlockIds);

    expect($result)->toHaveKeys(['block-1', 'block-2', 'block-3'])
        ->and($result)->not->toHaveKey('block-4');
});

test('collectRegionBlocks handles deeply nested blocks', function () {
    $allBlocks = [
        'block-1' => ['id' => 'block-1', 'type' => 'container', 'children' => ['block-2']],
        'block-2' => ['id' => 'block-2', 'type' => 'container', 'children' => ['block-3']],
        'block-3' => ['id' => 'block-3', 'type' => 'container', 'children' => ['block-4']],
        'block-4' => ['id' => 'block-4', 'type' => 'text'],
        'block-5' => ['id' => 'block-5', 'type' => 'image'],
    ];

    $rootBlockIds = ['block-1'];

    $result = $this->persistEditorUpdates->collectRegionBlocks($allBlocks, $rootBlockIds);

    expect($result)->toHaveKeys(['block-1', 'block-2', 'block-3', 'block-4'])
        ->and($result)->not->toHaveKey('block-5');
});

test('collectRegionBlocks handles multiple root blocks', function () {
    $allBlocks = [
        'block-1' => ['id' => 'block-1', 'type' => 'container', 'children' => ['block-2']],
        'block-2' => ['id' => 'block-2', 'type' => 'text'],
        'block-3' => ['id' => 'block-3', 'type' => 'container', 'children' => ['block-4']],
        'block-4' => ['id' => 'block-4', 'type' => 'image'],
        'block-5' => ['id' => 'block-5', 'type' => 'button'],
    ];

    $rootBlockIds = ['block-1', 'block-3'];

    $result = $this->persistEditorUpdates->collectRegionBlocks($allBlocks, $rootBlockIds);

    expect($result)->toHaveKeys(['block-1', 'block-2', 'block-3', 'block-4'])
        ->and($result)->not->toHaveKey('block-5');
});
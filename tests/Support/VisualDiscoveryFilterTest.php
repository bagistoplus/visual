<?php

use BagistoPlus\Visual\Support\VisualDiscoveryFilter;
use Craftile\Laravel\DiscoveryRoots;

function visualDiscoveryFilter(): array
{
    config()->set('craftile.discovery.enabled', false);

    $roots = new DiscoveryRoots(app());

    return [
        $roots,
        new VisualDiscoveryFilter($roots),
    ];
}

it('allows discovered block classes that belong to a registered block root namespace', function () {
    [$roots, $filter] = visualDiscoveryFilter();

    $roots->addBlockRoot('App\\Visual\\Blocks', base_path('app/Visual/Blocks'));

    expect($filter->allows(['class' => 'App\\Visual\\Blocks\\Hero'], 'block'))->toBeTrue()
        ->and($filter->allows(['class' => '\\App\\Visual\\Blocks\\Hero'], 'block'))->toBeTrue()
        ->and($filter->allows(['class' => 'App\\Visual\\Blocks'], 'block'))->toBeTrue()
        ->and($filter->allows(['class' => 'App\\Visual\\BlocksExtra\\Hero'], 'block'))->toBeFalse()
        ->and($filter->allows(['class' => 'Vendor\\Theme\\Blocks\\Hero'], 'block'))->toBeFalse();
});

it('allows discovered preset classes that belong to a registered preset root namespace', function () {
    [$roots, $filter] = visualDiscoveryFilter();

    $roots->addPresetRoot('App\\Visual\\Presets', base_path('app/Visual/Presets'));

    expect($filter->allows(['class' => 'App\\Visual\\Presets\\HeroPreset'], 'preset'))->toBeTrue()
        ->and($filter->allows(['class' => 'App\\Visual\\Blocks\\HeroPreset'], 'preset'))->toBeFalse();
});

it('rejects malformed entries, unknown types, and entries when no matching root exists', function () {
    [, $filter] = visualDiscoveryFilter();

    expect($filter->allows([], 'block'))->toBeFalse()
        ->and($filter->allows(['class' => null], 'block'))->toBeFalse()
        ->and($filter->allows(['class' => 'App\\Visual\\Blocks\\Hero'], 'unknown'))->toBeFalse()
        ->and($filter->allows(['class' => 'App\\Visual\\Blocks\\Hero'], 'block'))->toBeFalse();
});

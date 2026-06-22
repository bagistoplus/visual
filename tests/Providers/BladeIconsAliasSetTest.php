<?php

use BladeUI\Icons\Exceptions\SvgNotFound;
use BladeUI\Icons\Factory;
use BladeUI\Icons\IconsManifest;
use Illuminate\Support\Facades\Blade;

beforeEach(function () {
    config()->set('blade-icons.components.disabled', false);
    config()->set('bagisto_visual_iconmap', [
        'icon-compare' => 'lucide-scale',
    ]);

    app()->forgetInstance(Factory::class);

    $manifest = app(IconsManifest::class);
    $property = new ReflectionProperty($manifest, 'manifest');
    $property->setValue($manifest, null);

    app(Factory::class)->registerComponents();
});

it('renders mapped visual icon aliases through the svg helper', function () {
    expect(svg('icon-compare')->contents())
        ->toBe(svg('lucide-scale')->contents());
});

it('renders mapped visual icon aliases through the svg directive', function () {
    expect(Blade::render("@svg('icon-compare')"))
        ->toBe(svg('lucide-scale')->toHtml());
});

it('renders mapped visual icon aliases through explicit blade icon components', function () {
    expect(Blade::render('<x-icon-compare />'))
        ->toBe(svg('lucide-scale')->toHtml());
});

it('renders mapped visual icon aliases through the dynamic blade icon component', function () {
    expect(Blade::render('<x-icon name="icon-compare" />'))
        ->toBe(svg('lucide-scale')->toHtml());
});

it('falls back for unmapped visual icon aliases through the svg helper', function () {
    expect(svg('icon-not-mapped')->contents())
        ->toBe(svg('lucide-file-question')->contents());
});

it('falls back for unmapped visual icon aliases through the svg directive', function () {
    expect(Blade::render("@svg('icon-not-mapped')"))
        ->toBe(svg('lucide-file-question')->toHtml());
});

it('falls back for unmapped visual icon aliases through the dynamic blade icon component', function () {
    expect(Blade::render('<x-icon name="icon-not-mapped" />'))
        ->toBe(svg('lucide-file-question')->toHtml());
});

it('does not fall back for missing non visual icon names', function () {
    svg('visual-not-mapped');
})->throws(SvgNotFound::class);

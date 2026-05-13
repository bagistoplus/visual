<?php

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Sections\BladeSection;
use BagistoPlus\Visual\Sections\LivewireSection;
use BagistoPlus\Visual\VisualManager;
use Illuminate\Support\Facades\Blade;

it('registers Blade sections as Blade components', function () {
    Blade::shouldReceive('component')
        ->once()
        ->with(TestVisualSection::class, 'test-visual-section', 'visual-section-prefix');

    $visual = app()->make(VisualManager::class);
    $visual->registerSection(TestVisualSection::class, 'prefix');

    expect(Sections::get('prefix::test-visual-section')->isLivewire)->toBeFalse();
});

it('registers Livewire sections without registering a Blade component alias', function () {
    Blade::shouldReceive('component')->never();

    $visual = app()->make(VisualManager::class);
    $visual->registerSection(TestLivewireVisualSection::class, 'prefix');

    $section = Sections::get('prefix::test-livewire-visual-section');

    expect($section)->not->toBeNull();

    if ($section === null) {
        throw new RuntimeException('Livewire section was not registered.');
    }

    $rendered = $section->renderToBlade('section-id');

    expect($section->isLivewire)->toBeTrue();
    expect($rendered)->toContain('@livewire(\\'.TestLivewireVisualSection::class.'::class');
    expect($rendered)->not->toContain("@livewire('visual-section-");
});

class TestVisualSection extends BladeSection
{
    protected static string $slug = 'test-visual-section';
}

class TestLivewireVisualSection extends LivewireSection
{
    protected static string $slug = 'test-livewire-visual-section';
}

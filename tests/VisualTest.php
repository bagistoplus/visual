<?php

use BagistoPlus\Visual\VisualManager;
use Illuminate\Support\Facades\Blade;

it('registers a section and adds it to the Sections facade', function () {

    // Mock Blade component registration
    Blade::shouldReceive('component')
        ->once()
        ->with('TestSection', 'prefix-test-section', 'visual-section');

    // Instantiate Visual class and register the section
    $visual = app()->make(VisualManager::class);
    $visual->registerSection(TestSection::class, 'prefix');
})->todo();

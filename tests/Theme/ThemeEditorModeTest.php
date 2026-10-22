<?php

use BagistoPlus\Visual\Enums\ThemeEditorMode;
use BagistoPlus\Visual\Theme\Theme;

it('edits a visual theme in visual mode whatever bagisto offers', function () {
    fakeSectionSystem(available: false);

    $theme = Theme::make(['code' => 'visual-debut', 'name' => 'Debut', 'visual_theme' => true]);

    expect($theme->editorMode())->toBe(ThemeEditorMode::Visual)
        ->and($theme->editorMode()->isVisual())->toBeTrue()
        ->and($theme->editorMode()->isEditable())->toBeTrue();
});

it('edits a plain theme in bagisto mode when bagisto stores sections', function () {
    fakeSectionSystem(available: true);

    $theme = Theme::make(['code' => 'default', 'name' => 'Default']);

    expect($theme->editorMode())->toBe(ThemeEditorMode::Bagisto)
        ->and($theme->editorMode()->isBagisto())->toBeTrue();
});

it('leaves a plain theme unedited when bagisto has no section system', function () {
    fakeSectionSystem(available: false);

    $theme = Theme::make(['code' => 'default', 'name' => 'Default']);

    expect($theme->editorMode())->toBe(ThemeEditorMode::Unsupported)
        ->and($theme->editorMode()->isEditable())->toBeFalse();
});

it('customizes a visual theme in the visual editor by default', function () {
    fakeSectionSystem(available: true);

    $theme = Theme::make(['code' => 'visual-debut', 'name' => 'Debut', 'visual_theme' => true]);

    expect($theme->usesVisualEditor())->toBeTrue();
});

it('customizes a plain theme in bagisto\'s appearance editor by default', function () {
    fakeSectionSystem(available: true);

    $theme = Theme::make(['code' => 'default', 'name' => 'Default']);

    expect($theme->usesVisualEditor())->toBeFalse();
});

it('honours the editor a theme names for itself', function () {
    fakeSectionSystem(available: true);

    $plain = Theme::make([
        'code' => 'default',
        'name' => 'Default',
        'customize' => ['editor' => 'visual'],
    ]);

    $visual = Theme::make([
        'code' => 'visual-debut',
        'name' => 'Debut',
        'visual_theme' => true,
        'customize' => ['editor' => 'bagisto'],
    ]);

    expect($plain->usesVisualEditor())->toBeTrue()
        ->and($visual->usesVisualEditor())->toBeFalse();
});

it('customizes nothing in the visual editor when the theme cannot be edited', function () {
    fakeSectionSystem(available: false);

    $theme = Theme::make([
        'code' => 'default',
        'name' => 'Default',
        'customize' => ['editor' => 'visual'],
    ]);

    expect($theme->usesVisualEditor())->toBeFalse();
});

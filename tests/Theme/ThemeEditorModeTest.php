<?php

use BagistoPlus\Visual\Enums\ThemeEditorMode;
use BagistoPlus\Visual\Support\SectionSystem;
use BagistoPlus\Visual\Theme\Theme;

function fakeSectionSystem(bool $available, bool $registry = false): void
{
    app()->instance(SectionSystem::class, new class($available, $registry) extends SectionSystem
    {
        public function __construct(protected bool $isAvailable, protected bool $hasRegistry) {}

        public function available(): bool
        {
            return $this->isAvailable;
        }

        public function supportsTypeRegistry(): bool
        {
            return $this->hasRegistry;
        }
    });
}

it('edits a visual theme in visual mode whatever bagisto offers', function () {
    fakeSectionSystem(available: false);

    $theme = Theme::make(['code' => 'visual-debut', 'name' => 'Debut', 'visual_theme' => true]);

    expect($theme->editorMode())->toBe(ThemeEditorMode::Visual)
        ->and($theme->editorMode()->isVisual())->toBeTrue()
        ->and($theme->editorMode()->isEditable())->toBeTrue();
});

it('edits a plain theme in native mode when bagisto stores sections', function () {
    fakeSectionSystem(available: true);

    $theme = Theme::make(['code' => 'default', 'name' => 'Default']);

    expect($theme->editorMode())->toBe(ThemeEditorMode::Native)
        ->and($theme->editorMode()->isNative())->toBeTrue();
});

it('leaves a plain theme unedited when bagisto has no section system', function () {
    fakeSectionSystem(available: false);

    $theme = Theme::make(['code' => 'default', 'name' => 'Default']);

    expect($theme->editorMode())->toBe(ThemeEditorMode::Unsupported)
        ->and($theme->editorMode()->isEditable())->toBeFalse();
});

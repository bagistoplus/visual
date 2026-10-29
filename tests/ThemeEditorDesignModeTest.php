<?php

use BagistoPlus\Visual\ThemeEditor;
use Illuminate\Http\Request;

function editorAsking(string $url, array $headers = []): ThemeEditor
{
    app()->instance('request', Request::create($url, 'GET', [], [], [], $headers));

    return new ThemeEditor;
}

function bouncerAnswering(bool $allowed): void
{
    app()->instance('Webkul\User\Bouncer', new class($allowed)
    {
        public array $asked = [];

        public function __construct(protected bool $allowed) {}

        public function hasPermission(string $permission): bool
        {
            $this->asked[] = $permission;

            return $this->allowed;
        }
    });
}

it('drives the editor for someone who may edit a theme', function () {
    bouncerAnswering(true);

    expect(editorAsking('/?_designMode=default')->inDesignMode())->toBeTrue();
});

it('refuses someone who may not, however they ask', function (string $url, array $headers) {
    bouncerAnswering(false);

    expect(editorAsking($url, $headers)->inDesignMode())->toBeFalse();
})->with([
    'by query' => ['/?_designMode=default', []],
    'by header' => ['/', ['HTTP_X_VISUAL_EDITOR_THEME' => 'default']],
]);

it('drives the editor for a request that asks by header', function () {
    bouncerAnswering(true);

    expect(editorAsking('/', ['HTTP_X_VISUAL_EDITOR_THEME' => 'default'])->inDesignMode())->toBeTrue();
});

it('asks about the permission the editor itself is gated on', function () {
    bouncerAnswering(true);

    editorAsking('/?_designMode=default')->inDesignMode();

    expect(app('Webkul\User\Bouncer')->asked)->toBe(['bagisto_visual.editor']);
});

it('is off for a request that did not ask for it', function () {
    bouncerAnswering(true);

    expect(editorAsking('/')->inDesignMode())->toBeFalse();
});

it('lets the editor frame the storefront before it knows who is asking', function () {
    // The frame header is set before the session starts, so it is answered by
    // the request alone. What the page may say is decided later.
    bouncerAnswering(false);

    expect(editorAsking('/?_designMode=default')->designModeRequested())->toBeTrue()
        ->and(editorAsking('/')->designModeRequested())->toBeFalse();
});

it('is the editor request whoever made it, so what boots before a session can tell', function () {
    // Templates are registered while the application boots, before a session
    // says who is asking, and registering one says nothing to anyone.
    bouncerAnswering(false);

    expect(editorAsking('/?_designMode=default')->requested())->toBeTrue()
        ->and(editorAsking('/admin/visual/editor/default')->requested())->toBeTrue()
        ->and(editorAsking('/')->requested())->toBeFalse();
});

it('ignores a theme that is not registered, whatever mode asks for it', function (string $url) {
    $editor = editorAsking($url);

    expect($editor->designModeRequested())->toBeFalse()
        ->and($editor->previewModeRequested())->toBeFalse()
        ->and($editor->draftPreviewRequested())->toBeFalse()
        ->and($editor->usesEditorData())->toBeFalse();
})->with([
    'design mode' => ['/?_designMode=does-not-exist'],
    'theme preview' => ['/?_previewMode=does-not-exist'],
    'draft preview' => ['/?_draftPreview=does-not-exist'],
    'a key inside a theme config' => ['/?_previewMode=default.name'],
]);

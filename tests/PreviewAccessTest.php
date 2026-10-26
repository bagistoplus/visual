<?php

use BagistoPlus\Visual\Commands\GeneratePreviewCommand;
use BagistoPlus\Visual\Facades\ThemeEditor as ThemeEditorFacade;
use BagistoPlus\Visual\Middlewares\AllowSameOriginIframeInEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

function previewRequest(string $url, array $server = []): Request
{
    $request = Request::create($url, 'GET', [], [], [], $server);

    app()->instance('request', $request);
    app('url')->setRequest($request);

    return $request;
}

function bouncerGranting(string ...$permissions): void
{
    app()->instance('Webkul\User\Bouncer', new class($permissions)
    {
        public function __construct(protected array $permissions) {}

        public function hasPermission(string $permission): bool
        {
            return in_array($permission, $this->permissions, true);
        }
    });
}

function twoFactorPending(bool $pending): void
{
    app()->instance('Webkul\User\Http\Middleware\Bouncer', new class($pending)
    {
        public function __construct(protected bool $pending) {}

        public function isTwoFactorRequired(string $guard): bool
        {
            return $this->pending;
        }
    });
}

function signedOut(): void
{
    auth()->guard('admin')->forgetUser();
}

beforeEach(function () {
    Route::get('/', fn () => 'home')->name('shop.home.index');
});

describe('draft preview', function () {
    it('shows drafts to someone who may preview', function (string ...$permissions) {
        bouncerGranting(...$permissions);
        previewRequest('/?_draftPreview=fake-theme');

        expect(ThemeEditorFacade::inDraftPreviewMode())->toBeTrue()
            ->and(ThemeEditorFacade::usesEditorData())->toBeTrue();
    })->with([
        'preview permission' => ['bagisto_visual.preview'],
        'editor permission' => ['bagisto_visual.editor'],
    ]);

    it('shows the live store to anyone else, however they ask', function (string $url, array $server) {
        bouncerGranting('bagisto_visual.themes');
        previewRequest($url, $server);

        expect(ThemeEditorFacade::draftPreviewRequested())->toBeTrue()
            ->and(ThemeEditorFacade::inDraftPreviewMode())->toBeFalse()
            ->and(ThemeEditorFacade::usesEditorData())->toBeFalse();
    })->with([
        'by query' => ['/?_draftPreview=fake-theme', []],
        'by header' => ['/', ['HTTP_X_VISUAL_DRAFT_PREVIEW_THEME' => 'fake-theme']],
    ]);

    it('shows the live store to a visitor who is not signed in', function () {
        signedOut();
        previewRequest('/?_draftPreview=fake-theme');

        expect(ThemeEditorFacade::inDraftPreviewMode())->toBeFalse()
            ->and(ThemeEditorFacade::usesEditorData())->toBeFalse();
    });

    it('does not carry the preview onto links for someone who may not preview', function () {
        bouncerGranting();
        previewRequest('/?_draftPreview=fake-theme');

        expect(url('/products'))->not->toContain('_draftPreview');
    });
});

describe('theme preview', function () {
    it('previews another theme for someone who may preview', function () {
        bouncerGranting('bagisto_visual.preview');
        previewRequest('/?_previewMode=fake-theme');

        expect(ThemeEditorFacade::inPreviewMode())->toBeTrue()
            ->and(ThemeEditorFacade::activeTheme())->toBe('fake-theme')
            ->and(ThemeEditorFacade::usesEditorData())->toBeFalse();
    });

    it('shows the live store to someone who may not', function () {
        bouncerGranting('bagisto_visual.themes');
        previewRequest('/?_previewMode=fake-theme');

        expect(ThemeEditorFacade::previewModeRequested())->toBeTrue()
            ->and(ThemeEditorFacade::inPreviewMode())->toBeFalse();
    });

    it('previews through a link the application signed, without a session', function () {
        signedOut();
        previewRequest(URL::temporarySignedRoute('shop.home.index', now()->addMinutes(10), ['_previewMode' => 'fake-theme']));

        expect(ThemeEditorFacade::inPreviewMode())->toBeTrue();
    });

    it('refuses a signed link that expired or was changed', function (Closure $url) {
        signedOut();
        previewRequest($url());

        expect(ThemeEditorFacade::inPreviewMode())->toBeFalse();
    })->with([
        'expired' => fn () => URL::temporarySignedRoute('shop.home.index', now()->subMinute(), ['_previewMode' => 'fake-theme']),
        'other theme' => fn () => str_replace('_previewMode=fake-theme', '_previewMode=other', URL::temporarySignedRoute('shop.home.index', now()->addMinutes(10), ['_previewMode' => 'fake-theme'])),
    ]);

    it('signs the link the screenshot command opens', function () {
        $url = app(GeneratePreviewCommand::class)->previewUrl('fake-theme');

        signedOut();
        previewRequest($url);

        expect($url)->toContain('_previewMode=fake-theme')
            ->toContain('expires=')
            ->toContain('signature=')
            ->and(ThemeEditorFacade::inPreviewMode())->toBeTrue();
    });
});

describe('the administrator', function () {
    it('must have an active account', function () {
        $this->signInAdmin(['status' => 0]);
        bouncerGranting('bagisto_visual.editor', 'bagisto_visual.preview');

        previewRequest('/?_draftPreview=fake-theme');
        expect(ThemeEditorFacade::inDraftPreviewMode())->toBeFalse();

        previewRequest('/?_designMode=fake-theme');
        expect(ThemeEditorFacade::inDesignMode())->toBeFalse();
    });

    it('must have completed two factor', function () {
        twoFactorPending(true);
        bouncerGranting('bagisto_visual.editor', 'bagisto_visual.preview');

        previewRequest('/?_draftPreview=fake-theme');
        expect(ThemeEditorFacade::inDraftPreviewMode())->toBeFalse();

        previewRequest('/?_designMode=fake-theme');
        expect(ThemeEditorFacade::inDesignMode())->toBeFalse();
    });

    it('may preview once two factor is done', function () {
        twoFactorPending(false);
        bouncerGranting('bagisto_visual.preview');
        previewRequest('/?_draftPreview=fake-theme');

        expect(ThemeEditorFacade::inDraftPreviewMode())->toBeTrue();
    });
});

it('lets a theme preview be framed before it knows who is asking', function () {
    signedOut();
    $request = previewRequest('/?_previewMode=fake-theme');

    $response = (new AllowSameOriginIframeInEditor)->handle($request, fn () => new Response('page'));

    expect($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN');
});

it('offers a permission to preview without editing', function () {
    $entry = collect(config('acl'))->firstWhere('key', 'bagisto_visual.preview');

    expect($entry)->not->toBeNull()
        ->and($entry['route'])->toBe([])
        ->and(trans($entry['name']))->toBe('Preview drafts and themes');
});

describe('the themes page', function () {
    function themesPage(): string
    {
        view()->addNamespace('admin', __DIR__.'/views/admin');

        return view('visual::admin.themes.index', [
            'themes' => collect([[
                'code' => 'fake-theme',
                'name' => 'Fake Theme',
                'screenshot' => 'fake.png',
                'author' => null,
                'version' => null,
                'description' => null,
                'documentationUrl' => null,
            ]]),
        ])->render();
    }

    it('offers each action only to who may use it', function (array $permissions, bool $customize, bool $preview) {
        bouncerGranting(...$permissions);
        previewRequest('/admin/visual/themes');

        $page = themesPage();

        expect(str_contains($page, 'visual/editor/fake-theme'))->toBe($customize)
            ->and(str_contains($page, '_previewMode=fake-theme'))->toBe($preview)
            ->and(str_contains($page, '_draftPreview=fake-theme'))->toBe($preview);
    })->with([
        'editor' => [['bagisto_visual.editor'], true, true],
        'reviewer' => [['bagisto_visual.preview'], false, true],
        'theme list only' => [['bagisto_visual.themes'], false, false],
    ]);
});

it('does not keep an answer given before the session started', function () {
    // Something asks before the session starts, when nobody is signed in yet. The
    // same request asked again once the session is known must see the editor.
    signedOut();
    bouncerGranting('bagisto_visual.editor');
    $request = previewRequest('/?_designMode=fake-theme');

    expect(ThemeEditorFacade::inDesignMode())->toBeFalse();

    $request->setLaravelSession(app('session')->driver());
    $this->signInAdmin();

    expect(ThemeEditorFacade::inDesignMode())->toBeTrue();
});

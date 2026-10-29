<?php

use BagistoPlus\Visual\Middlewares\UseShopThemeFromRequest;
use Illuminate\Http\Request;
use Webkul\Core\Models\Channel;
use Webkul\Theme\Facades\Themes as ThemesFacade;

function channelThemeAfter(string $url): ?string
{
    ThemesFacade::swap(new class
    {
        public ?string $code = null;

        public function exists(string $code): bool
        {
            return true;
        }

        public function set(string $code): void
        {
            $this->code = $code;
        }

        public function current(): mixed
        {
            return null;
        }
    });

    $channel = new Channel;
    $channel->id = 1;
    $channel->code = 'default';
    $channel->theme = 'visual-debut';
    core()->setCurrentChannel($channel);

    $request = Request::create($url);
    app()->instance('request', $request);

    (new UseShopThemeFromRequest)->handle($request, fn () => 'next');

    return core()->getCurrentChannel()->theme;
}

it('names the previewed theme on the channel, where Bagisto reads its sections', function (string $url) {
    expect(channelThemeAfter($url))->toBe('default');
})->with([
    'design mode' => ['/?_designMode=default'],
    'theme preview' => ['/?_previewMode=default'],
    'draft preview' => ['/?_draftPreview=default'],
]);

it('leaves the channel its theme for a visitor', function () {
    auth()->guard('admin')->forgetUser();

    expect(channelThemeAfter('/?_previewMode=default'))->toBe('visual-debut');
});

it('leaves the channel its theme when the previewed theme is not registered', function (string $url) {
    expect(channelThemeAfter($url))->toBe('visual-debut');
})->with([
    'design mode' => ['/?_designMode=does-not-exist'],
    'theme preview' => ['/?_previewMode=does-not-exist'],
    'draft preview' => ['/?_draftPreview=does-not-exist'],
]);

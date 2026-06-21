<?php

use BagistoPlus\Visual\Tests\TestCase;
use Illuminate\Support\Collection;
use Konekt\Concord\Contracts\Concord;
use Konekt\Concord\Contracts\Convention;
use Webkul\Core\Contracts\Channel as ChannelContract;
use Webkul\Core\Contracts\Locale as LocaleContract;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\ChannelProxy;
use Webkul\Core\Models\Locale;
use Webkul\Core\Models\LocaleProxy;
use Webkul\Theme\Facades\Themes as ThemesFacade;

uses(TestCase::class)->in(__DIR__);

if (! function_exists('themes')) {
    function themes()
    {
        return ThemesFacade::getFacadeRoot();
    }
}

if (! function_exists('core')) {
    function core()
    {
        if (! app()->bound('visual.test.core')) {
            app()->instance('visual.test.core', new class
            {
                private ?Channel $currentChannel = null;

                private string $requestedChannelCode = 'default';

                private string $requestedLocaleCode = 'en';

                private ?Collection $channels = null;

                public function getRequestedChannelCode(): string
                {
                    return $this->requestedChannelCode;
                }

                public function setRequestedChannelCode(string $channel): void
                {
                    $this->requestedChannelCode = $channel;
                }

                public function getDefaultChannelCode(): string
                {
                    return 'default';
                }

                public function getRequestedLocaleCode(): string
                {
                    return $this->requestedLocaleCode;
                }

                public function setRequestedLocaleCode(string $locale): void
                {
                    $this->requestedLocaleCode = $locale;
                }

                public function getAllChannels(): Collection
                {
                    return $this->channels ?? collect([
                        (object) [
                            'code' => 'default',
                            'name' => 'Default',
                            'locales' => collect([
                                (object) ['code' => 'en', 'name' => 'English'],
                                (object) ['code' => 'ar', 'name' => 'Arabic'],
                            ]),
                            'default_locale' => (object) ['code' => 'en'],
                        ],
                    ]);
                }

                public function setChannels(array $channels): void
                {
                    $this->channels = collect($channels);
                }

                public function getConfigData(string $key): mixed
                {
                    return match ($key) {
                        'catalog.products.search.engine' => 'database',
                        default => null,
                    };
                }

                public function getCurrentChannel(): Channel
                {
                    return $this->currentChannel ??= $this->channel('default');
                }

                public function setCurrentChannel(Channel $channel): void
                {
                    $this->currentChannel = $channel;
                }

                private function channel(string $code): Channel
                {
                    $channel = new Channel;
                    $channel->id = 1;
                    $channel->code = $code;

                    return $channel;
                }
            });
        }

        return app('visual.test.core');
    }
}

function visualBindConcordModelsForTests(): void
{
    $concord = Mockery::mock(Concord::class);
    $convention = Mockery::mock(Convention::class);

    $convention->shouldReceive('modelForProxy')
        ->with(ChannelProxy::class)
        ->andReturn(Channel::class);

    $convention->shouldReceive('modelForProxy')
        ->with(LocaleProxy::class)
        ->andReturn(Locale::class);

    $convention->shouldReceive('contractForModel')
        ->with(Channel::class)
        ->andReturn(ChannelContract::class);

    $convention->shouldReceive('contractForModel')
        ->with(Locale::class)
        ->andReturn(LocaleContract::class);

    $concord->shouldReceive('getConvention')->andReturn($convention);

    $concord->shouldReceive('model')
        ->with(ChannelContract::class)
        ->andReturn(Channel::class);

    $concord->shouldReceive('model')
        ->with(LocaleContract::class)
        ->andReturn(Locale::class);

    app()->instance('concord', $concord);

    ChannelProxy::__reset();
    LocaleProxy::__reset();
}

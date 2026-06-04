<?php

use BagistoPlus\Visual\Tests\TestCase;
use Konekt\Concord\Contracts\Concord;
use Konekt\Concord\Contracts\Convention;
use Webkul\Core\Contracts\Channel as ChannelContract;
use Webkul\Core\Contracts\Locale as LocaleContract;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\ChannelProxy;
use Webkul\Core\Models\Locale;
use Webkul\Core\Models\LocaleProxy;

uses(TestCase::class)->in(__DIR__);

if (! function_exists('core')) {
    function core()
    {
        return new class
        {
            public function getRequestedChannelCode(): string
            {
                return 'default';
            }

            public function getDefaultChannelCode(): string
            {
                return 'default';
            }

            public function getRequestedLocaleCode(): string
            {
                return 'en';
            }

            public function getConfigData(string $key): mixed
            {
                return match ($key) {
                    'catalog.products.search.engine' => 'database',
                    default => null,
                };
            }

            public function getCurrentChannel(): object
            {
                return (object) ['id' => 1];
            }
        };
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

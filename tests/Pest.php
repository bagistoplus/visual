<?php

use BagistoPlus\Visual\Data\BagistoSectionType;
use BagistoPlus\Visual\Support\SectionSystem;
use BagistoPlus\Visual\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

if (! function_exists('migrateThemeDataTables')) {
    function migrateThemeDataTables(): void
    {
        $migration = include __DIR__.'/../database/migrations/2026_10_28_000001_create_visual_theme_data_tables.php';
        $default = DB::getDefaultConnection();

        DB::setDefaultConnection($migration->getConnection() ?? $default);
        $migration->up();
        DB::setDefaultConnection($default);
    }
}

if (! function_exists('countThemeDataQueries')) {
    function countThemeDataQueries(callable $callback): int
    {
        $count = 0;
        DB::listen(function ($query) use (&$count) {
            if (str_contains($query->sql, 'visual_theme_')) {
                $count++;
            }
        });

        $callback();

        return $count;
    }
}

if (! function_exists('fakeSectionSystem')) {
    function fakeSectionSystem(bool $available): void
    {
        app()->instance(
            SectionSystem::class,
            new class($available) extends SectionSystem
            {
                public function __construct(protected bool $isAvailable) {}

                public function available(): bool
                {
                    return $this->isAvailable;
                }
            }
        );
    }
}

if (! function_exists('offerTypes')) {
    function offerTypes(array $types): void
    {
        app()->instance(SectionSystem::class, new class($types) extends SectionSystem
        {
            public function __construct(protected array $offered) {}

            public function available(): bool
            {
                return true;
            }

            public function types(?string $themeCode = null): Collection
            {
                return collect($this->offered);
            }
        });
    }
}

if (! function_exists('carouselType')) {
    function carouselType(): BagistoSectionType
    {
        return new BagistoSectionType(
            code: 'image_carousel',
            title: 'Image Carousel',
            icon: 'icon-image',
            singleton: false,
            pinned: false,
            rendersInLayout: false,
            view: 'shop::sections.image_carousel',
            fieldsResolver: fn () => [
                ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                [
                    'key' => 'images',
                    'type' => 'repeater',
                    'label' => 'Slider',
                    'fields' => [
                        ['key' => 'image', 'type' => 'image', 'label' => 'Image'],
                        ['key' => 'link', 'type' => 'text', 'label' => 'Link'],
                    ],
                ],
            ],
        );
    }
}

if (! function_exists('footerType')) {
    function footerType(): BagistoSectionType
    {
        return new BagistoSectionType(
            code: 'footer_links',
            title: 'Footer Links',
            icon: 'icon-list',
            singleton: true,
            pinned: true,
            rendersInLayout: true,
            view: 'shop::sections.footer_links',
            fieldsResolver: fn () => [
                [
                    'key' => 'columns',
                    'type' => 'repeater',
                    'label' => 'Columns',
                    'fields' => [
                        [
                            'key' => 'links',
                            'type' => 'repeater',
                            'label' => 'Links',
                            'fields' => [['key' => 'url', 'type' => 'text', 'label' => 'URL']],
                        ],
                    ],
                ],
            ],
        );
    }
}

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
                            'id' => 1,
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

                public function getRequestedChannel(): Channel
                {
                    return $this->channel($this->requestedChannelCode);
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

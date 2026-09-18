<?php

namespace BagistoPlus\Visual\Tests\Providers;

use BagistoPlus\Visual\Facades\ThemePathsResolver as ThemePathsResolverFacade;
use BagistoPlus\Visual\Tests\Fixtures\EarlyBoot\EarlyBootTestCase;
use BagistoPlus\Visual\Theme\Themes;
use BagistoPlus\Visual\ThemePathsResolver;
use BagistoPlus\Visual\ThemeSettingsLoader;
use ReflectionProperty;

class ViewServiceProviderBindingsTest extends EarlyBootTestCase
{
    public function test_it_shares_one_resolver_with_a_loader_resolved_before_the_package_booted(): void
    {
        $loader = app(ThemeSettingsLoader::class);
        $property = new ReflectionProperty($loader, 'themePathsResolver');

        $this->assertSame(app(ThemePathsResolver::class), $property->getValue($loader));
        $this->assertSame(app(ThemePathsResolver::class), ThemePathsResolverFacade::getFacadeRoot());
    }

    public function test_it_resolves_the_visual_themes_class(): void
    {
        $this->assertInstanceOf(Themes::class, themes());
    }
}

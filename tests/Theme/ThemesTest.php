<?php

use BagistoPlus\Visual\Theme\Themes;

it('should extends \\Webkul\\Theme\\Themes', function () {
    expect(get_parent_class(Themes::class))->toBe(\Webkul\Theme\Themes::class);
});

test('`app(\'themes\')` should resolve instance of \\BagistoPlus\\Visual\\Theme\Themes', function () {
    expect(app('themes'))
        ->toBeInstanceOf(\BagistoPlus\Visual\Theme\Themes::class);
});

it('should load themes as instance of \\BagistoPlus\\Visual\\Theme\\Theme', function () {
    expect(app('themes')->all())
        ->toContainOnlyInstancesOf(\BagistoPlus\Visual\Theme\Theme::class);
});

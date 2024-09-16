<?php

use BagistoPlus\Visual\Theme\Theme;

it('should extends \\Webkul\\Theme\\Theme', function () {
    expect(get_parent_class(Theme::class))
        ->toBeClass(\Webkul\Theme\Theme::class);
});

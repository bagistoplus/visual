<?php

use BagistoPlus\Visual\Support\SectionIcons;

it('draws the icons bagisto names as the matching lucide ones', function () {
    $icons = new SectionIcons;

    expect($icons->svg('icon-image'))->toStartWith('<svg')
        ->and($icons->svg('icon-list'))->toStartWith('<svg')
        ->and($icons->svg('icon-image'))->not->toBe($icons->svg('icon-list'));
});

it('leaves a type that hands over its own markup alone', function () {
    $markup = '<svg viewBox="0 0 24 24"><path d="M0 0h24"/></svg>';

    expect((new SectionIcons)->svg($markup))->toBe($markup);
});

it('falls back for an icon it does not know, and for none at all', function () {
    $icons = new SectionIcons;

    expect($icons->svg('icon-something-invented'))->toStartWith('<svg')
        ->and($icons->svg(null))->toStartWith('<svg')
        ->and($icons->svg('icon-something-invented'))->toBe($icons->svg(''));
});

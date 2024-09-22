<?php

use BagistoPlus\Visual\Sections\Section;
use BagistoPlus\Visual\Sections\SectionRepository;

it('starts with an empty collection', function () {
    $repository = new SectionRepository;

    expect($repository->all())->toBeEmpty();
});

it('adds sections to the repository', function () {
    $repository = new SectionRepository;

    $section = new Section('test-section', 'Test Section', 'div');
    $repository->add($section);

    expect($repository->all())
        ->toHaveCount(1)
        ->toHaveKey('test-section', $section);
});

it('checks if the repository has a section by slug', function () {
    $repository = new SectionRepository;

    $section = new Section('test-section', 'Test Section', 'div');
    $repository->add($section);

    expect($repository->has('test-section'))->toBeTrue();
    expect($repository->has('non-existent-slug'))->toBeFalse();
});

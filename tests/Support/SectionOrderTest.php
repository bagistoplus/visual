<?php

use BagistoPlus\Visual\Support\SectionOrder;

it('keeps the order a store holds when nothing in it moved', function () {
    // The layout's services sit between two content sections, where Bagisto
    // left a section it appended.
    expect(SectionOrder::merge([1, 2, 11, 14, 13], [[1, 2, 14], [11, 13]]))
        ->toBe([1, 2, 11, 14, 13]);
});

it('moves a section among the ones it is drawn with', function () {
    expect(SectionOrder::merge([1, 2, 11, 14, 13], [[2, 1, 14], [11, 13]]))
        ->toBe([2, 1, 11, 14, 13]);
});

it('puts a new section last, moving none of the others', function () {
    expect(SectionOrder::merge([1, 2, 11, 13], [[1, 2, 20], [11, 13]]))
        ->toBe([1, 2, 11, 13, 20]);
});

it('keeps new sections in the order each list holds them', function () {
    expect(SectionOrder::merge([1, 11], [[20, 1, 21], [11, 22]]))
        ->toBe([20, 11, 1, 21, 22]);
});

it('drops a section that is no longer held', function () {
    expect(SectionOrder::merge([1, 8, 2, 11], [[1, 2], [11]]))
        ->toBe([1, 2, 11]);
});

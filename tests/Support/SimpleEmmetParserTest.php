<?php

use BagistoPlus\Visual\Support\SimpleEmmetParser;

it('parses basic tag without class, id, or content', function () {
    $input = 'div';
    $expected = '<div></div>';
    expect(SimpleEmmetParser::parse($input))->toBe($expected);
});

it('parses tag with class only', function () {
    $input = 'div.container';
    $expected = '<div class="container"></div>';
    expect(SimpleEmmetParser::parse($input))->toBe($expected);
});

it('parses tag with id only', function () {
    $input = 'div#header';
    $expected = '<div id="header"></div>';
    expect(SimpleEmmetParser::parse($input))->toBe($expected);
});

it('parses tag with id and class (id first)', function () {
    $input = 'div#header.container';
    $expected = '<div id="header" class="container"></div>';
    expect(SimpleEmmetParser::parse($input))->toBe($expected);
});

it('parses tag with content', function () {
    $input = 'div{__content__}';
    $expected = '<div>__content__</div>';
    expect(SimpleEmmetParser::parse($input))->toBe($expected);
});

it('parses tag with id, class, and content', function () {
    $input = 'div#header.container{__content__}';
    $expected = '<div id="header" class="container">__content__</div>';
    expect(SimpleEmmetParser::parse($input))->toBe($expected);
});

it('parses nested tags with id first, class after', function () {
    $input = 'div#container.container>header#id.header{__content__}';
    $expected = '<div id="container" class="container"><header id="id" class="header">__content__</header></div>';
    expect(SimpleEmmetParser::parse($input))->toBe($expected);
});

it('parses complex nested tags with multiple ids and classes', function () {
    $input = 'div#main.wrapper>section#content.content>p#description.text{Text here}';
    $expected = '<div id="main" class="wrapper"><section id="content" class="content"><p id="description" class="text">Text here</p></section></div>';
    expect(SimpleEmmetParser::parse($input))->toBe($expected);
});

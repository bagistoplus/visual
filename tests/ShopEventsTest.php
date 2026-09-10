<?php

use BagistoPlus\Visual\Enums\Events;

it('keeps the storefront events module aligned with the Events constants', function () {
    $modulePath = dirname(__DIR__).'/resources/assets/shop/events.ts';

    expect($modulePath)->toBeReadableFile();

    preg_match(
        '/export const VisualEvents = \{(?<body>.*?)\} as const;/s',
        file_get_contents($modulePath),
        $matches
    );

    // Guards the regex above: if the literal is renamed or reformatted, fail loudly here rather
    // than silently comparing an empty map.
    expect($matches)->toHaveKey('body');

    preg_match_all("/(?<name>[A-Z0-9_]+):\s*'(?<value>[^']+)'/", $matches['body'], $entries, PREG_SET_ORDER);

    $fromTypescript = collect($entries)
        ->mapWithKeys(fn (array $entry) => [$entry['name'] => $entry['value']])
        ->sortKeys()
        ->all();

    $fromPhp = collect((new ReflectionClass(Events::class))->getConstants())
        ->sortKeys()
        ->all();

    // Compared as whole maps so a constant missing from either side fails, in either direction.
    expect($fromTypescript)->toBe($fromPhp);
});

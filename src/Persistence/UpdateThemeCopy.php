<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Theme\ThemeCopies;
use InvalidArgumentException;

class UpdateThemeCopy
{
    public function __construct(
        protected ThemeDataStore $dataStore,
        protected ThemeCopies $copies,
    ) {}

    public function handle(string $themeCode, string $name, ?string $description = null): void
    {
        $meta = $this->copies->all()[$themeCode] ?? null;

        if ($meta === null) {
            throw new InvalidArgumentException("[{$themeCode}] is not a theme copy.");
        }

        $this->dataStore->putMeta($themeCode, array_merge($meta, [
            'name' => $name,
            'description' => $description,
        ]));

        $this->copies->forget();
    }
}

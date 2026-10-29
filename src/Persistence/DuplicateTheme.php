<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Data\ThemeSnapshot;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\Theme\ThemeCopies;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class DuplicateTheme
{
    public function __construct(
        protected ThemeDataStore $dataStore,
        protected ThemeCopies $copies,
    ) {}

    /**
     * Copy what the editor and the storefront hold for a visual theme to a new
     * theme, and return its code.
     */
    public function handle(string $themeCode, string $name, ?string $description = null): string
    {
        $theme = Theme::forCode($themeCode);

        if (! $theme || ! $theme->isVisualTheme()) {
            throw new InvalidArgumentException("[{$themeCode}] is not a visual theme.");
        }

        $parentCode = $this->copies->all()[$themeCode]['parent'] ?? $themeCode;
        $code = $this->newCode($parentCode);
        $snapshot = $this->dataStore->snapshot($themeCode);

        try {
            $this->dataStore->restore($code, new ThemeSnapshot(
                editor: $snapshot->editor,
                live: $snapshot->live,
                edited: $snapshot->edited,
            ));

            $this->dataStore->putMeta($code, [
                'parent' => $parentCode,
                'name' => $name,
                'description' => $description,
                'created_at' => now()->toIso8601String(),
            ]);
        } catch (Throwable $e) {
            $this->dataStore->deleteTheme($code);

            throw $e;
        }

        $this->copies->forget();

        return $code;
    }

    protected function newCode(string $parentCode): string
    {
        $taken = $this->dataStore->themes();

        do {
            $code = $parentCode.'-'.Str::lower(Str::random(6));
        } while (config()->has("themes.shop.{$code}") || in_array($code, $taken, true));

        return $code;
    }
}

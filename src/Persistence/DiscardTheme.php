<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Contracts\ThemeDataStore;

/**
 * Throw away every unpublished change a theme is holding in its theme data.
 *
 * Used for themes whose sections Bagisto stores, where the editor offers a
 * discard button in place of undo. A visual theme has undo instead and never
 * reaches this.
 */
class DiscardTheme
{
    public function __construct(
        protected ThemeDataStore $dataStore,
        protected ThemeSettingsCache $settingsCache,
    ) {}

    public function handle(string $themeCode): void
    {
        $this->dataStore->discard($themeCode);

        $this->settingsCache->forgetAll($themeCode);
    }
}

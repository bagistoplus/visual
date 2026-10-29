<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Theme\ThemeCopies;
use DomainException;
use InvalidArgumentException;
use Spatie\ResponseCache\Facades\ResponseCache;

class DeleteThemeCopy
{
    public function __construct(
        protected ThemeDataStore $dataStore,
        protected ThemeCopies $copies,
        protected ThemeSettingsCache $settingsCache,
    ) {}

    /**
     * Remove a copy and everything its data store holds for it. A copy a
     * channel is set to is kept, since that channel would fall back to the
     * default theme without anyone choosing it.
     */
    public function handle(string $themeCode): void
    {
        if (! $this->copies->isCopy($themeCode)) {
            throw new InvalidArgumentException("[{$themeCode}] is not a theme copy.");
        }

        $channels = $this->channelsUsing($themeCode);

        if ($channels !== []) {
            throw new DomainException(trans('visual::admin.themes.copy-in-use', [
                'channels' => implode(', ', $channels),
            ]));
        }

        $this->dataStore->deleteTheme($themeCode);
        $this->settingsCache->forgetAll($themeCode);
        $this->copies->forget();

        if (class_exists(ResponseCache::class)) {
            ResponseCache::clear();
        }
    }

    /**
     * @return array<int, string>
     */
    protected function channelsUsing(string $themeCode): array
    {
        return core()->getAllChannels()
            ->filter(fn ($channel) => ($channel->theme ?? null) === $themeCode)
            ->map(fn ($channel) => $channel->name ?? $channel->code)
            ->values()
            ->all();
    }
}

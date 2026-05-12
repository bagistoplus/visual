<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Theme\Theme;
use Webkul\Core\Models\Channel;

class ChannelThemeResolver
{
    public function resolve(Channel|string|null $channel = null): ?Theme
    {
        if (is_string($channel)) {
            $channel = Channel::query()->where('code', $channel)->first();
        }

        $channel = $channel instanceof Channel
            ? $channel
            : Channel::query()->where('code', core()->getCurrentChannelCode())->first();

        if (! $channel) {
            return null;
        }

        $themeCode = $channel->getAttribute('theme');

        if (! $themeCode || ! isset(config('themes.shop', [])[$themeCode])) {
            return null;
        }

        $theme = Theme::make(array_merge(['code' => $themeCode], config("themes.shop.{$themeCode}")));

        return $theme->isVisualTheme() ? $theme : null;
    }

    public function resolveDefault(): ?Theme
    {
        return $this->resolve(core()->getDefaultChannel());
    }
}

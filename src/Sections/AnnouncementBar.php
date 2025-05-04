<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Sections\Settings\Color;
use BagistoPlus\Visual\Sections\Settings\Link;
use BagistoPlus\Visual\Sections\Settings\Text;

class AnnouncementBar extends BladeSection
{
    protected static string $view = 'shop::sections.announcement-bar';

    public static function settings(): array
    {
        return [
            Text::make('text', __('visual::sections.announcement-bar.settings.text_label'))
                ->default(__('visual::sections.announcement-bar.settings.default_text')),

            Link::make('link', __('visual::sections.announcement-bar.settings.link_label')),

            Color::make('background_color', __('visual::sections.announcement-bar.settings.background_color_label'))
                ->default('#4f46e5'),

            Color::make('text_color', __('visual::sections.announcement-bar.settings.background_color_label'))
                ->default('#ffffff'),
        ];
    }
}

<?php

namespace BagistoPlus\Visual\Sections;

class Newsletter extends BladeSection
{
    protected static string $view = 'shop::sections.newsletter';

    public static function settings(): array
    {
        return [
            Settings\Text::make('title', __('visual::sections.newsletter.settings.title_label'))
                ->default(__('visual::sections.newsletter.settings.title_default')),

            Settings\RichText::make('description', __('visual::sections.newsletter.settings.description_label'))
                ->default(__('visual::sections.newsletter.settings.description_default')),

            Settings\Header::make(__('visual::sections.newsletter.settings.custom_design_label')),

            Settings\Color::make('background_color', __('visual::sections.newsletter.settings.background_color_label'))
                ->default('#4f46e5'),

            Settings\Color::make('text_color', __('visual::sections.newsletter.settings.text_color_label'))
                ->default('#ffffff'),

            Settings\Color::make('button_color', __('visual::sections.newsletter.settings.button_color_label'))
                ->default('#6366f1'),

            Settings\Color::make('button_text_color', __('visual::sections.newsletter.settings.button_text_color_label'))
                ->default('#ffffff'),
        ];
    }
}

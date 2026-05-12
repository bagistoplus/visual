<?php

namespace BagistoPlus\Visual\Actions\Admin;

use BagistoPlus\Visual\Support\ChannelThemeResolver;
use BagistoPlus\Visual\Support\TemplateAssignment;

class PersistTemplateAssignment
{
    public function __construct(
        protected TemplateAssignment $assignment,
        protected ChannelThemeResolver $themes,
    ) {}

    public function __invoke($model, string $type): void
    {
        if (! request()->has('visual_template')) {
            return;
        }

        $theme = $type === 'product'
            ? $this->themes->resolve(request('channel') ?: core()->getRequestedChannelCode())
            : $this->themes->resolveDefault();

        $channel = $type === 'product'
            ? (request('channel') ?: core()->getRequestedChannelCode())
            : core()->getDefaultChannelCode();
        $locale = request('locale') ?: core()->getRequestedLocaleCode();
        $template = request('visual_template') ?: null;

        if (! $this->assignment->isValid($template, $type, $theme, $channel, $locale)) {
            session()->flash('warning', __('visual::admin.template-assignment.unavailable'));

            return;
        }

        $this->assignment->save($model, $type, $template, $channel, $locale);
    }
}

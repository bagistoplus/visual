<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Models\VisualTemplateAssignment;
use Webkul\CMS\Models\Page;

class CmsPageVisualEditorUrlResolver
{
    public function __construct(
        protected ChannelThemeResolver $themes,
        protected TemplateDiscovery $templates,
    ) {}

    public function forRow(object $row): ?string
    {
        return $this->forTemplate(
            template: $row->visual_template ?? null,
            urlKey: $row->url_key,
            locale: $row->locale ?? core()->getRequestedLocaleCode(),
        );
    }

    public function forPage(?Page $page): ?string
    {
        if (! $page) {
            return null;
        }

        $locale = request('locale') ?: core()->getRequestedLocaleCode();
        $translation = $page->translate($locale);
        $urlKey = $translation['url_key'] ?? null;

        if (! $urlKey) {
            return null;
        }

        return $this->forTemplate(
            template: $this->assignedTemplate($page, $locale),
            urlKey: $urlKey,
            locale: $locale,
        );
    }

    public function forTemplate(?string $template, string $urlKey, string $locale): ?string
    {
        $theme = $this->themes->resolveDefault();

        if (! $theme) {
            return null;
        }

        $channel = core()->getDefaultChannelCode();
        $template = $this->validCustomPageTemplate($template, $theme, $channel, $locale);

        if (! $template) {
            return null;
        }

        return route('visual.admin.editor', ['theme' => $theme->code]).'?'.http_build_query([
            'template' => $template,
            'previewUrl' => route('shop.cms.page', $urlKey),
            'channel' => $channel,
            'locale' => $locale,
        ]);
    }

    protected function assignedTemplate(Page $page, string $locale): ?string
    {
        if (! Visual::templateAssignmentsEnabled()) {
            return null;
        }

        return VisualTemplateAssignment::query()
            ->where('assignable_id', $page->getKey())
            ->where('template_type', 'page')
            ->whereNull('channel')
            ->where('locale', $locale)
            ->whereIn('assignable_type', array_unique([
                $page->getMorphClass(),
                Page::class,
                get_class($page),
                'Webkul\CMS\Contracts\Page',
            ]))
            ->value('template_key');
    }

    protected function validCustomPageTemplate(?string $template, $theme, string $channel, string $locale): ?string
    {
        if (
            $template
            && $template !== 'page'
            && $this->templates->typeForKey($template) === 'page'
            && $this->templates->exists($theme, $template, 'page', $channel, $locale, true)
        ) {
            return $template;
        }

        return null;
    }
}

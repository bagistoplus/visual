<?php

namespace BagistoPlus\Visual\Actions\Admin;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Support\ChannelThemeResolver;
use BagistoPlus\Visual\Support\TemplateAssignment;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Webkul\Theme\ViewRenderEventManager;

class AddTemplateAssignmentField
{
    public function __construct(
        protected ChannelThemeResolver $themes,
        protected TemplateAssignment $assignments,
        protected TemplateDiscovery $templates,
    ) {}

    public function __invoke(ViewRenderEventManager $viewRenderEventManager, string $type, ?string $modelParam = null): void
    {
        $data = $this->data(
            type: $type,
            model: $viewRenderEventManager->getParam($modelParam ?? $type),
        );

        if (! $data['enabled']) {
            return;
        }

        $viewRenderEventManager->addTemplate(
            view()->make('visual::admin.template-assignment.field', $data)->render()
        );
    }

    public function data(string $type, mixed $model): array
    {
        if (! Visual::templateAssignmentsEnabled()) {
            return [
                'enabled' => false,
                'type' => $type,
                'model' => $model,
                'accordion' => $this->usesAccordion($type),
            ];
        }

        if (! $model instanceof Model) {
            return [
                'enabled' => false,
                'type' => $type,
                'model' => $model,
                'accordion' => $this->usesAccordion($type),
            ];
        }

        $channel = $this->channel($type);
        $locale = request('locale', core()->getRequestedLocaleCode());
        $theme = $this->theme($type, $channel);

        if (! $theme) {
            return [
                'enabled' => false,
                'type' => $type,
                'model' => $model,
                'accordion' => $this->usesAccordion($type),
            ];
        }

        return [
            'enabled' => true,
            'type' => $type,
            'model' => $model,
            'accordion' => $this->usesAccordion($type),
            'theme' => $theme,
            'templates' => $this->templates->forType($theme, $type, $channel, $locale, false)
                ->reject(fn ($template) => $template->key === $type),
            'selected' => old('visual_template', $this->assignments->read($model, $type, $channel, $locale)),
            'defaultLabel' => __('visual::admin.template-assignment.default', ['type' => Str::headline($type)]),
        ];
    }

    protected function channel(string $type): string
    {
        return $type === 'product'
            ? $this->selectedProductChannel()
            : core()->getDefaultChannelCode();
    }

    protected function theme(string $type, string $channel)
    {
        return $type === 'product'
            ? $this->themes->resolve($channel)
            : $this->themes->resolveDefault();
    }

    protected function selectedProductChannel(): string
    {
        return request('channel') ?: core()->getRequestedChannelCode();
    }

    protected function usesAccordion(string $type): bool
    {
        return in_array($type, ['category', 'page']);
    }
}

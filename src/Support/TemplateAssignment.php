<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Models\VisualTemplateAssignment;
use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TemplateAssignment
{
    public function __construct(
        protected TemplateDiscovery $templates,
        protected ChannelThemeResolver $themes,
    ) {}

    public function resolve(Model $model, string $type, ?Theme $theme = null, ?string $channel = null, ?string $locale = null, bool $includeEditorDrafts = false): string
    {
        $theme ??= $type === 'product'
            ? $this->themes->resolve($channel ?? core()->getRequestedChannelCode())
            : $this->themes->resolveDefault();

        if (! $theme) {
            return $type;
        }

        $assigned = $this->read($model, $type, $channel, $locale);

        if (
            $assigned
            && $this->templates->typeForKey($assigned) === $type
            && $this->templates->exists($theme, $assigned, $type, $channel, $locale, $includeEditorDrafts)
        ) {
            return $assigned;
        }

        return $type;
    }

    public function read(Model $model, string $type, ?string $channel = null, ?string $locale = null): ?string
    {
        if (! in_array($type, TemplateDiscovery::ASSIGNABLE_TYPES, true)) {
            return null;
        }

        $assignment = $this->findAssignment(
            model: $model,
            type: $type,
            channel: $this->normalizeChannel($type, $channel),
            locale: $locale ?? core()->getRequestedLocaleCode(),
        );

        return $assignment['template_key'] ?? null;
    }

    public function save(Model $model, string $type, ?string $template, ?string $channel = null, ?string $locale = null): void
    {
        $template = $template ?: null;
        $channel = $this->normalizeChannel($type, $channel);
        $locale ??= core()->getRequestedLocaleCode();

        if (! in_array($type, TemplateDiscovery::ASSIGNABLE_TYPES, true)) {
            return;
        }

        $identity = [
            'assignable_type' => $model->getMorphClass(),
            'assignable_id' => $model->getKey(),
            'template_type' => $type,
            'channel' => $channel,
            'locale' => $locale,
        ];

        if ($template === null) {
            VisualTemplateAssignment::query()
                ->where($identity)
                ->delete();

            $model->unsetRelation('visualTemplateAssignments');

            return;
        }

        VisualTemplateAssignment::query()->updateOrCreate(
            $identity,
            [
                'template_key' => $template,
            ]
        );

        $model->unsetRelation('visualTemplateAssignments');
    }

    public function isValid(?string $template, string $type, ?Theme $theme, ?string $channel, ?string $locale): bool
    {
        if (! $template) {
            return true;
        }

        return $theme
            && $this->templates->typeForKey($template) === $type
            && $this->templates->exists($theme, $template, $type, $channel, $locale);
    }

    protected function normalizeChannel(string $type, ?string $channel): ?string
    {
        return $type === 'product'
            ? ($channel ?? core()->getRequestedChannelCode())
            : null;
    }

    protected function findAssignment(Model $model, string $type, ?string $channel, string $locale): ?array
    {
        if ($model->relationLoaded('visualTemplateAssignments')) {
            $assignment = $model->getRelation('visualTemplateAssignments')
                ->first(fn ($assignment) => $this->matchesAssignment($assignment, $type, $channel, $locale));

            return $assignment ? $this->assignmentToArray($assignment) : null;
        }

        $assignment = VisualTemplateAssignment::query()
            ->where([
                'assignable_type' => $model->getMorphClass(),
                'assignable_id' => $model->getKey(),
                'template_type' => $type,
                'channel' => $channel,
                'locale' => $locale,
            ])
            ->first();

        return $assignment ? $this->assignmentToArray($assignment) : null;
    }

    protected function matchesAssignment(mixed $assignment, string $type, ?string $channel, string $locale): bool
    {
        $data = $this->assignmentToArray($assignment);

        return ($data['template_type'] ?? null) === $type
            && ($data['channel'] ?? null) === $channel
            && ($data['locale'] ?? null) === $locale;
    }

    protected function assignmentToArray(mixed $assignment): array
    {
        if ($assignment instanceof Model) {
            return $assignment->getAttributes();
        }

        if ($assignment instanceof Collection) {
            return $assignment->all();
        }

        if (is_object($assignment)) {
            return get_object_vars($assignment);
        }

        return (array) $assignment;
    }
}

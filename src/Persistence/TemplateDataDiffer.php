<?php

namespace BagistoPlus\Visual\Persistence;

final class TemplateDataDiffer
{
    public function __construct(
        protected EditorDataStore $store,
        protected LocalizedProperties $localizedProperties,
    ) {}

    public function clean(array $current, array $source): array
    {
        return $source === []
            ? $current
            : $this->store->merge($source, $this->store->diff($current, $source));
    }

    public function forceLocalizedValues(array $current, array $diff, ?string $currentLocale, ?string $parentLocale): array
    {
        if (! $parentLocale || $currentLocale === $parentLocale) {
            return $diff;
        }

        return $this->store->merge($diff, $this->localizedProperties->blockFragment($current));
    }
}

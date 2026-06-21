<?php

namespace BagistoPlus\Visual\Persistence;

final class ThemeSettingsDiffer
{
    public function __construct(protected EditorDataStore $store) {}

    public function clean(array $current, array $parent): array
    {
        return $parent === []
            ? $current
            : $this->store->merge($parent, $this->store->diff($current, $parent));
    }
}

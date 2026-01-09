<?php

namespace BagistoPlus\Visual\Settings;

class TypographyPresets extends Base
{
    protected static string $type = 'typography_presets';

    protected array $presets = [];

    public function presets(array $presets): static
    {
        $this->meta['presets'] = $presets;
        $this->default($presets);

        return $this;
    }
}

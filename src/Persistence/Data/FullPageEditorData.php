<?php

namespace BagistoPlus\Visual\Persistence\Data;

use Illuminate\Support\Collection;

final class FullPageEditorData
{
    public function __construct(
        public readonly string $theme,
        public readonly string $channel,
        public readonly string $locale,
        public readonly string $template,
        public readonly array $blocks,
        public readonly array $regions,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            theme: $data['theme'],
            channel: $data['channel'],
            locale: $data['locale'],
            template: $data['template'],
            blocks: $data['page']['blocks'] ?? [],
            regions: $data['page']['regions'] ?? [],
        );
    }

    public function sharedRegions(): Collection
    {
        return collect($this->regions)
            ->filter(fn ($region) => isset($region['shared']) && $region['shared'] === true);
    }

    public function nonSharedRegions(): Collection
    {
        return collect($this->regions)
            ->filter(fn ($region) => ! isset($region['shared']) || $region['shared'] === false);
    }
}

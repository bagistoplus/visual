<?php

namespace BagistoPlus\Visual\Persistence\Data;

use Craftile\Laravel\Data\UpdateRequest;
use Illuminate\Support\Collection;

final class EditorUpdateData
{
    public function __construct(
        public readonly string $theme,
        public readonly string $channel,
        public readonly string $locale,
        public readonly string $templateName,
        public readonly string $templateUrl,
        public readonly array $sources,
        public readonly array $blocks,
        public readonly array $regions,
        public readonly array $changes,
        public readonly UpdateRequest $updateRequest,
    ) {}

    public static function fromValidated(array $data): self
    {
        $updates = $data['updates'];

        return new self(
            theme: $data['theme'],
            channel: $data['channel'],
            locale: $data['locale'],
            templateName: $data['template']['name'],
            templateUrl: $data['template']['url'],
            sources: decrypt($data['template']['sources']),
            blocks: $updates['blocks'],
            regions: $updates['regions'],
            changes: $updates['changes'],
            updateRequest: UpdateRequest::make($updates),
        );
    }

    public function sharedRegions(): Collection
    {
        return collect($this->updateRequest->regions)
            ->filter(fn ($region) => isset($region['shared']) && $region['shared'] === true);
    }

    public function nonSharedRegions(): Collection
    {
        return collect($this->updateRequest->regions)
            ->filter(fn ($region) => ! isset($region['shared']) || $region['shared'] === false);
    }

    public function changedBlockIds(): array
    {
        return array_keys($this->blocks);
    }

    public function addedBlockIds(): array
    {
        return $this->changes['added'] ?? [];
    }
}

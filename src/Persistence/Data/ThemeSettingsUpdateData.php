<?php

namespace BagistoPlus\Visual\Persistence\Data;

final class ThemeSettingsUpdateData
{
    public function __construct(
        public readonly string $theme,
        public readonly string $channel,
        public readonly string $locale,
        public readonly string $templateUrl,
        public readonly array $updates,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            theme: $data['theme'],
            channel: $data['channel'],
            locale: $data['locale'],
            templateUrl: $data['template']['url'],
            updates: $data['updates'],
        );
    }
}

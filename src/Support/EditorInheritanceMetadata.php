<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Persistence\EditorDataStore;

class EditorInheritanceMetadata
{
    public function __construct(
        protected EditorDataStore $editorDataStore,
        protected TemplateDiscovery $templateDiscovery,
    ) {}

    public function localeInheritanceForTemplate(string $theme, string $channel, string $template): array
    {
        $logicalPath = $this->templateDiscovery->templateStoragePath($template);
        $selectedChannel = core()->getAllChannels()->firstWhere('code', $channel);
        $inheritance = [];

        foreach ($selectedChannel->locales as $locale) {
            $parent = $this->editorDataStore->storedParent(
                $theme,
                $this->editorDataStore->relativePath($channel, $locale->code, $logicalPath)
            );

            if (! $parent) {
                continue;
            }

            [$parentChannel, $parentLocale] = explode('/', $parent, 3);

            $inheritance[$locale->code] = [
                'parentChannel' => $parentChannel,
                'parentLocale' => $parentLocale,
            ];
        }

        return $inheritance;
    }
}

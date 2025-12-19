<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use Craftile\Laravel\Data\UpdateRequest;
use Craftile\Laravel\Support\HandleUpdates;
use Illuminate\Support\Facades\File;

class PersistEditorUpdates
{
    public function __construct(
        protected HandleUpdates $handleUpdates,
    ) {}

    public function handle(array $data): array
    {
        $theme = $data['theme'];
        $channel = $data['channel'];
        $locale = $data['locale'];
        $template = $data['template']['name'];
        $sources = decrypt($data['template']['sources']);
        $updateRequest = UpdateRequest::make($data['updates']);

        $sharedRegions = collect($updateRequest->regions)->filter(fn ($region) => isset($region['shared']) && $region['shared'] === true);
        $nonSharedRegions = collect($updateRequest->regions)->filter(fn ($region) => ! isset($region['shared']) || $region['shared'] === false);

        $allBlocks = [];

        foreach ($sharedRegions as $region) {
            $result = $this->persistSharedRegion($region, $updateRequest, $theme, $channel, $locale, $sources);
            if ($result['updated'] ?? false) {
                $allBlocks = array_merge($allBlocks, $result['data']['blocks'] ?? []);
            }
        }

        if ($nonSharedRegions->isNotEmpty()) {
            $result = $this->persistTemplateRegions(
                $nonSharedRegions->toArray(),
                $updateRequest,
                $theme,
                $channel,
                $locale,
                $template,
                $sources
            );
            if ($result['updated'] ?? false) {
                $allBlocks = array_merge($allBlocks, $result['data']['blocks'] ?? []);
            }
        }

        return [
            'loadedBlocks' => $allBlocks,
        ];
    }

    public function handleFullPage(array $data): void
    {
        $theme = $data['theme'];
        $channel = $data['channel'];
        $locale = $data['locale'];
        $template = $data['template'];
        $page = $data['page'];

        $allBlocks = $page['blocks'] ?? [];
        $regions = collect($page['regions'] ?? []);
        $sharedRegions = $regions->filter(fn ($region) => isset($region['shared']) && $region['shared'] === true);
        $nonSharedRegions = $regions->filter(fn ($region) => ! isset($region['shared']) || $region['shared'] === false);

        foreach ($sharedRegions as $region) {
            $regionPath = $this->getRegionFilePath($theme, $channel, $locale, $region['name']);

            $regionBlocks = $this->collectRegionBlocks($allBlocks, $region['blocks'] ?? []);
            $regionData = [
                'blocks' => $regionBlocks,
                'regions' => [$region],
            ];

            $this->saveFlattened($regionData, $regionPath, $theme);
        }

        if ($nonSharedRegions->isNotEmpty()) {
            $templatePath = $this->getTemplateFilePath($theme, $channel, $locale, $template);

            $rootBlockIds = $nonSharedRegions->flatMap(fn ($region) => $region['blocks'] ?? [])->unique()->toArray();
            $templateBlocks = $this->collectRegionBlocks($allBlocks, $rootBlockIds);
            $templateData = [
                'blocks' => $templateBlocks,
                'regions' => $nonSharedRegions->toArray(),
            ];

            $this->saveFlattened($templateData, $templatePath, $theme);
        }
    }

    public function collectRegionBlocks(array $allBlocks, array $rootBlockIds): array
    {
        $collectedBlocks = [];
        $toProcess = $rootBlockIds;

        while (! empty($toProcess)) {
            $blockId = array_shift($toProcess);

            if (isset($allBlocks[$blockId]) && ! isset($collectedBlocks[$blockId])) {
                $block = $allBlocks[$blockId];
                $collectedBlocks[$blockId] = $block;

                if (isset($block['children']) && is_array($block['children'])) {
                    $toProcess = array_merge($toProcess, $block['children']);
                }
            }
        }

        return $collectedBlocks;
    }

    protected function persistSharedRegion(array $region, UpdateRequest $updateRequest, string $theme, string $channel, string $locale, array $sources): array
    {
        $regionPath = $this->getRegionFilePath($theme, $channel, $locale, $region['name']);
        $sourceDataPath = $regionPath;

        if (! File::exists($sourceDataPath)) {
            $sourceDataPath = $this->getRegionSourcePath($region['name'], $sources);
        }

        $result = $this->handleUpdates->execute($sourceDataPath, $updateRequest, [$region['name']]);

        if ($result['updated']) {
            $this->saveFlattened($result['data'], $regionPath, $theme);
        }

        return $result;
    }

    protected function persistTemplateRegions(array $nonSharedRegions, UpdateRequest $updateRequest, string $theme, string $channel, string $locale, string $template, array $sources): array
    {
        $templatePath = $this->getTemplateFilePath($theme, $channel, $locale, $template);
        $sourceDataPath = $templatePath;

        if (! File::exists($sourceDataPath)) {
            $sourceDataPath = $this->getTemplateSourcePath($template, $sources);
        }

        $regionNames = collect($nonSharedRegions)->pluck('name')->toArray();
        $result = $this->handleUpdates->execute($sourceDataPath, $updateRequest, $regionNames);

        if ($result['updated']) {
            $this->saveFlattened($result['data'], $templatePath, $theme);
        }

        return $result;
    }

    protected function saveFlattened(array $data, string $filePath, string $theme): void
    {
        File::ensureDirectoryExists(dirname($filePath));
        File::put($filePath, $this->encodeJson($data));

        // Mark that edits have been made
        $lastEditFile = ThemePathsResolver::getThemeBaseDataPath($theme, 'editor/.last-edit');
        File::put($lastEditFile, (string) time());
    }

    protected function encodeJson(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function getTemplateFilePath(string $theme, string $channel, string $locale, string $template): string
    {
        return ThemePathsResolver::resolvePath(
            themeCode: $theme,
            channel: $channel,
            locale: $locale,
            mode: 'editor',
            path: "templates/{$template}.json"
        );
    }

    protected function getRegionFilePath(string $theme, string $channel, string $locale, string $regionName): string
    {
        return ThemePathsResolver::resolvePath(
            themeCode: $theme,
            channel: $channel,
            locale: $locale,
            mode: 'editor',
            path: "regions/{$regionName}.json"
        );
    }

    protected function getRegionSourcePath(string $regionName, array $sources): ?string
    {
        return collect($sources)
            ->first(fn ($sourcePath) => str_contains($sourcePath, "/regions/{$regionName}."));
    }

    protected function getTemplateSourcePath(string $template, array $sources): ?string
    {
        return collect($sources)
            ->first(fn ($sourcePath) => str_contains($sourcePath, "/templates/{$template}."));
    }
}

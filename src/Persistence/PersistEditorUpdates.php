<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use Craftile\Laravel\Data\UpdateRequest;
use Craftile\Laravel\Support\HandleUpdates;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

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

        foreach ($sharedRegions as $region) {
            $this->persistSharedRegion($region, $updateRequest, $theme, $channel, $locale, $sources);
        }

        // Process template regions
        if ($nonSharedRegions->isNotEmpty()) {
            $this->persistTemplateRegions(
                $nonSharedRegions->toArray(),
                $updateRequest,
                $theme,
                $channel,
                $locale,
                $template,
                $sources
            );
        }

        return [
            'success' => true,
            'message' => 'Updates persisted successfully',
            'updatedSections' => $this->extractUpdatedSections($updateRequest),
        ];
    }

    protected function persistSharedRegion(array $region, UpdateRequest $updateRequest, string $theme, string $channel, string $locale, array $sources): void
    {
        $regionSourcePath = $this->getRegionSourcePath($region['name'], $sources);
        $sourceData = $regionSourcePath ? $this->loadJsonView($regionSourcePath) : [];

        $result = $this->handleUpdates->execute($sourceData, $updateRequest, [$region['name']]);

        if ($result['updated']) {
            $regionPath = $this->getRegionFilePath(
                $theme,
                $channel,
                $locale,
                $region['name']
            );

            $this->saveFlattened($result['data'], $regionPath);
        }
    }

    protected function persistTemplateRegions(array $nonSharedRegions, UpdateRequest $updateRequest, string $theme, string $channel, string $locale, string $template, array $sources): void
    {
        $templateSourcePath = $this->getTemplateSourcePath($template, $sources);
        $sourceData = $templateSourcePath ? $this->loadJsonView($templateSourcePath) : [];

        $regionNames = collect($nonSharedRegions)->pluck('name')->toArray();
        $result = $this->handleUpdates->execute($sourceData, $updateRequest, $regionNames);

        if ($result['updated']) {
            $templatePath = $this->getTemplateFilePath(
                $theme,
                $channel,
                $locale,
                $template
            );

            $this->saveFlattened($result['data'], $templatePath);
        }
    }

    protected function extractUpdatedSections(UpdateRequest $updateRequest): array
    {
        return collect($updateRequest->getAddedBlocks())
            ->merge($updateRequest->getUpdatedBlocks())
            ->map(fn ($block) => $block['sectionId'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    protected function saveFlattened(array $data, string $filePath): void
    {
        File::ensureDirectoryExists(dirname($filePath));
        File::put($filePath, $this->encodeJson($data));
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

    protected function loadJsonView(string $filePath): array
    {
        if (! File::exists($filePath)) {
            return [];
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return match ($extension) {
            'json' => $this->loadJsonFile($filePath),
            'yaml', 'yml' => $this->loadYamlFile($filePath),
            default => []
        };
    }

    protected function loadJsonFile(string $filePath): array
    {
        try {
            $content = File::get($filePath);
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Failed to parse JSON file {$filePath}: ".json_last_error_msg());
            }

            return $data ?: [];
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to load JSON file {$filePath}: ".$e->getMessage(), 0, $e);
        }
    }

    protected function loadYamlFile(string $filePath): array
    {
        try {
            return Yaml::parseFile($filePath) ?? [];
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to load YAML file {$filePath}: ".$e->getMessage(), 0, $e);
        }
    }
}

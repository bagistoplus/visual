<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Events\ThemeActivated;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Persistence\Data\EditorUpdateData;
use BagistoPlus\Visual\Persistence\Data\FullPageEditorData;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Theme\Theme;
use Craftile\Laravel\Data\UpdateRequest;
use Craftile\Laravel\Facades\Craftile;
use Craftile\Laravel\Support\HandleUpdates;
use Craftile\Laravel\View\JsonViewParser;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Webkul\Core\Models\Channel;

class PersistEditorUpdates
{
    public function __construct(
        protected HandleUpdates $handleUpdates,
        protected EditorDataStore $editorDataStore,
        protected TemplateDataDiffer $templateDataDiffer,
        protected TemplateDiscovery $templateDiscovery,
    ) {}

    public function handle(EditorUpdateData $data): array
    {
        return $this->withinStorefrontContext($data->theme, $data->channel, $data->locale, function () use ($data) {
            $allBlocks = [];

            foreach ($data->sharedRegions() as $region) {
                $result = $this->persistPartialRegionUpdate($region, $data->updateRequest, $data->theme, $data->channel, $data->locale, $data->sources);
                if ($result['updated'] ?? false) {
                    $allBlocks = array_merge($allBlocks, $result['data']['blocks'] ?? []);
                }
            }

            $nonSharedRegions = $data->nonSharedRegions();

            if ($nonSharedRegions->isNotEmpty()) {
                $result = $this->persistPartialTemplateUpdate(
                    $nonSharedRegions->toArray(),
                    $data->updateRequest,
                    $data->theme,
                    $data->channel,
                    $data->locale,
                    $data->templateName,
                    $data->sources
                );
                if ($result['updated'] ?? false) {
                    $allBlocks = array_merge($allBlocks, $result['data']['blocks'] ?? []);
                }
            }

            return [
                'loadedBlocks' => $allBlocks,
            ];
        });
    }

    public function handleFullPage(FullPageEditorData $data): void
    {
        $this->withinStorefrontContext($data->theme, $data->channel, $data->locale, function () use ($data) {
            foreach ($data->sharedRegions() as $region) {
                $regionBlocks = $this->collectRegionBlocks($data->blocks, $region['blocks'] ?? []);

                $this->persistRegionData(
                    region: $region,
                    data: [
                        'blocks' => $regionBlocks,
                        'regions' => [$region],
                    ],
                    theme: $data->theme,
                    channel: $data->channel,
                    locale: $data->locale,
                    sources: []
                );
            }

            $nonSharedRegions = $data->nonSharedRegions();

            if ($nonSharedRegions->isNotEmpty()) {
                $rootBlockIds = $nonSharedRegions->flatMap(fn ($region) => $region['blocks'] ?? [])->unique()->toArray();
                $templateBlocks = $this->collectRegionBlocks($data->blocks, $rootBlockIds);

                $this->persistTemplateData(
                    data: [
                        'blocks' => $templateBlocks,
                        'regions' => $nonSharedRegions->toArray(),
                    ],
                    theme: $data->theme,
                    channel: $data->channel,
                    locale: $data->locale,
                    template: $data->template,
                    sources: []
                );
            }
        });
    }

    public function collectRegionBlocks(array $allBlocks, array $rootBlockIds): array
    {
        $collected = [];
        $queue = $rootBlockIds;
        $index = 0;

        while (isset($queue[$index])) {
            $blockId = $queue[$index++];

            if (isset($collected[$blockId]) || ! isset($allBlocks[$blockId])) {
                continue;
            }

            $collected[$blockId] = $allBlocks[$blockId];

            foreach ($allBlocks[$blockId]['children'] ?? [] as $childId) {
                $queue[] = $childId;
            }
        }

        return $collected;
    }

    protected function persistPartialRegionUpdate(array $region, UpdateRequest $updateRequest, string $theme, string $channel, string $locale, array $sources): array
    {
        $regionKey = $this->regionKey($region);
        $logicalPath = "regions/{$regionKey}.json";
        $relativePath = $this->editorDataStore->relativePath($channel, $locale, $logicalPath);
        $regionPath = $this->getRegionFilePath($theme, $channel, $locale, $regionKey);
        $parent = $this->selectParent($theme, $channel, $locale, $relativePath, $logicalPath, $sources);
        $sourceDataPath = $regionPath;

        if (! File::exists($sourceDataPath)) {
            $sourceDataPath = $parent ? $this->editorDataStore->path($theme, $parent) : null;
        }

        if (! $sourceDataPath || ! File::exists($sourceDataPath)) {
            $sourceDataPath = $this->getRegionSourcePath($regionKey, $sources);
        }

        $result = $this->executeUpdates($sourceDataPath, $updateRequest, [$regionKey]);

        if ($result['updated']) {
            $this->persistRegionData($region, $result['data'], $theme, $channel, $locale, $sources, $sourceDataPath);
        }

        return $result;
    }

    protected function persistPartialTemplateUpdate(array $nonSharedRegions, UpdateRequest $updateRequest, string $theme, string $channel, string $locale, string $template, array $sources): array
    {
        $templatePath = $this->getTemplateFilePath($theme, $channel, $locale, $template);
        $logicalPath = $this->templateDiscovery->templateStoragePath($template);
        $relativePath = $this->editorDataStore->relativePath($channel, $locale, $logicalPath);
        $parent = $this->selectParent($theme, $channel, $locale, $relativePath, $logicalPath, $sources);

        $sourceDataPath = $templatePath;
        if (! File::exists($sourceDataPath)) {
            $sourceDataPath = $parent ? $this->editorDataStore->path($theme, $parent) : null;
        }

        if (! $sourceDataPath || ! File::exists($sourceDataPath)) {
            $sourceDataPath = $this->getTemplateSourcePath($template, $sources);
        }

        $regionKeys = collect($nonSharedRegions)
            ->map(fn ($region) => $this->regionKey($region))
            ->toArray();

        $result = $this->executeUpdates($sourceDataPath, $updateRequest, $regionKeys);

        if ($result['updated']) {
            $this->persistTemplateData($result['data'], $theme, $channel, $locale, $template, $sources, $sourceDataPath);
        }

        return $result;
    }

    protected function persistRegionData(array $region, array $data, string $theme, string $channel, string $locale, array $sources, ?string $sourceDataPath = null): void
    {
        $regionKey = $this->regionKey($region);
        $logicalPath = "regions/{$regionKey}.json";
        $relativePath = $this->editorDataStore->relativePath($channel, $locale, $logicalPath);
        $parent = $this->selectParent($theme, $channel, $locale, $relativePath, $logicalPath, $sources);
        $sourceDataPath ??= $this->getRegionFilePath($theme, $channel, $locale, $regionKey);
        $sourceData = $this->sourceDataForSave($theme, $sourceDataPath, $parent, null);

        $this->saveEditorData($theme, $relativePath, $parent, $data, $sourceData);
    }

    protected function persistTemplateData(array $data, string $theme, string $channel, string $locale, string $template, array $sources, ?string $sourceDataPath = null): void
    {
        $logicalPath = $this->templateDiscovery->templateStoragePath($template);
        $relativePath = $this->editorDataStore->relativePath($channel, $locale, $logicalPath);
        $parent = $this->selectParent($theme, $channel, $locale, $relativePath, $logicalPath, $sources);
        $sourceDataPath ??= $this->getTemplateFilePath($theme, $channel, $locale, $template);
        $sourceData = $this->sourceDataForSave($theme, $sourceDataPath, $parent, null);

        $this->saveEditorData($theme, $relativePath, $parent, $data, $sourceData);
    }

    protected function executeUpdates(?string $sourceDataPath, UpdateRequest $updateRequest, array $regionKeys): array
    {
        return $this->handleUpdates->execute($sourceDataPath, $updateRequest, $regionKeys);
    }

    protected function withinStorefrontContext(string $theme, string $channel, string $locale, callable $callback): mixed
    {
        $previousLocale = app()->getLocale();
        /** @var Channel $previousChannel */
        $previousChannel = core()->getCurrentChannel();
        $nextChannel = $this->resolveChannel($channel);

        app()->get(JsonViewParser::class)->clearCache();
        app()->setLocale($locale);

        core()->setCurrentChannel($nextChannel);

        Craftile::detectPreviewUsing(fn () => true);
        ThemeActivated::dispatch($this->theme($theme));
        Craftile::registerDiscoveredSchemas();

        try {
            return $callback();
        } finally {
            app()->setLocale($previousLocale);

            core()->setCurrentChannel($previousChannel);

            Craftile::detectPreviewUsing(fn () => ThemeEditor::inDesignMode());
            app()->get(JsonViewParser::class)->clearCache();
        }
    }

    protected function theme(string $theme): Theme
    {
        return Theme::make(array_merge(['code' => $theme, 'name' => $theme], config("themes.shop.{$theme}", [])));
    }

    protected function resolveChannel(string $channel): Channel
    {
        /** @var Channel $resolvedChannel */
        $resolvedChannel = Channel::query()->where('code', $channel)->firstOrFail();

        return $resolvedChannel;
    }

    protected function saveEditorData(string $theme, string $relativePath, ?string $parent, array $current, array $sourceData): void
    {
        $clean = $this->templateDataDiffer->clean($current, $sourceData);
        $parentData = $parent ? $this->editorDataStore->loadResolved($theme, $parent) : [];
        $resolvedCurrent = $parent ? $this->editorDataStore->merge($parentData, $clean) : $clean;
        $diff = $parent ? $this->editorDataStore->diff($resolvedCurrent, $parentData) : $clean;
        $diff = $this->templateDataDiffer->forceLocalizedValues(
            current: $resolvedCurrent,
            diff: $diff,
            currentLocale: $this->editorDataStore->localeFromRelative($relativePath),
            parentLocale: $parent ? $this->editorDataStore->localeFromRelative($parent) : null,
        );

        $this->editorDataStore->save($theme, $relativePath, $diff, $parent);
    }

    protected function selectParent(string $theme, string $channel, string $locale, string $relativePath, string $logicalPath, array $sources): ?string
    {
        return $this->editorDataStore->storedParent($theme, $relativePath)
            ?? $this->editorDataStore->parentFromSources($theme, $logicalPath, $sources, $relativePath)
            ?? $this->editorDataStore->nearestFallbackParent($theme, $channel, $locale, $logicalPath);
    }

    protected function sourceDataForSave(string $theme, ?string $sourceDataPath, ?string $parent, ?array $fallback): array
    {
        if ($sourceDataPath && File::exists($sourceDataPath)) {
            return app(JsonViewParser::class)->parse($sourceDataPath);
        }

        if ($parent) {
            return $this->editorDataStore->loadResolved($theme, $parent);
        }

        return $fallback ?? [];
    }

    protected function getTemplateFilePath(string $theme, string $channel, string $locale, string $template): string
    {
        return ThemePathsResolver::resolvePath(
            themeCode: $theme,
            channel: $channel,
            locale: $locale,
            mode: 'editor',
            path: $this->templateDiscovery->templateStoragePath($template)
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
        $templatePath = $this->templateDiscovery->templateStoragePath($template);
        $sourcePatterns = ['/'.Str::beforeLast($templatePath, '.json').'.'];

        if ($this->templateDiscovery->typeForKey($template) === $template) {
            $sourcePatterns[] = "/templates/{$template}/index.";
        }

        return collect($sources)->first(fn ($sourcePath) => collect($sourcePatterns)
            ->contains(fn ($pattern) => str_contains($sourcePath, $pattern)));
    }

    protected function regionKey(array $region): string
    {
        return $region['id'] ?? $region['name'];
    }
}

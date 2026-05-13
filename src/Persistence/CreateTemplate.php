<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Data\TemplateFile;
use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Theme\Theme;
use Craftile\Laravel\View\JsonViewParser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateTemplate
{
    public function __construct(protected TemplateDiscovery $templates) {}

    public function __invoke(
        Theme $theme,
        string $channel,
        string $locale,
        string $type,
        string $name,
        ?string $basedOn
    ): array {
        $slug = Str::slug(Str::limit($name, 25, ''));
        $key = sprintf('%s.%s', $type, $slug);

        if (! $slug || ! in_array($type, TemplateDiscovery::ASSIGNABLE_TYPES)) {
            throw new \InvalidArgumentException(__('visual::theme-editor.create_template_errors.invalid_name_or_type'));
        }

        $path = ThemePathsResolver::resolvePath($theme->code, $channel, $locale, 'editor', "templates/{$type}/{$slug}.json");

        if (File::exists($path)) {
            throw new \InvalidArgumentException(__('visual::theme-editor.create_template_errors.already_exists'));
        }

        $data = $this->buildTemplateData($theme, $channel, $locale, $type, $basedOn);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        File::put(ThemePathsResolver::getThemeBaseDataPath($theme->code, 'editor/.last-edit'), (string) time());

        return [
            'key' => $key,
            'type' => $type,
            'label' => Str::headline(str_replace('-', ' ', $slug)),
            'path' => $path,
        ];
    }

    protected function buildTemplateData(
        Theme $theme,
        string $channel,
        string $locale,
        string $type,
        ?string $basedOn
    ): array {
        $blocks = [];
        $regions = [];

        if ($basedOn && $basedOn !== '__empty__') {
            $base = $this->templates->forType($theme, $type, $channel, $locale, true)
                ->firstWhere('key', $basedOn);

            if (! $base?->isJsonTemplate) {
                throw new \InvalidArgumentException(__('visual::theme-editor.create_template_errors.base_not_found'));
            }

            $baseData = $this->loadTemplateData($base);

            if ($baseData) {
                $regions = collect($baseData['regions'] ?? [])
                    ->filter(fn ($region) => ($region['shared'] ?? false) !== true)
                    ->values()
                    ->all();

                $blocks = $this->collectBlocks(
                    $baseData['blocks'] ?? [],
                    collect($regions)->flatMap(fn ($region) => $region['blocks'] ?? [])->unique()->values()->all()
                );
            }
        }

        if (collect($regions)->where('shared', false)->isEmpty()) {
            $regions[] = ['name' => 'main', 'shared' => false, 'blocks' => []];
        }

        return [
            'blocks' => $blocks,
            'regions' => array_values($regions),
        ];
    }

    protected function loadTemplateData(TemplateFile $template): array
    {
        if (! $template->path || ! File::exists($template->path)) {
            return [
                'blocks' => [],
                'regions' => [
                    ['name' => 'main', 'shared' => false, 'blocks' => []],
                ],
            ];
        }

        return app(JsonViewParser::class)->parse($template->path);
    }

    protected function collectBlocks(array $allBlocks, array $rootBlockIds): array
    {
        $blocks = [];
        $queue = $rootBlockIds;

        while ($queue !== []) {
            $id = array_shift($queue);

            if (! isset($allBlocks[$id]) || isset($blocks[$id])) {
                continue;
            }

            $blocks[$id] = $allBlocks[$id];
            $queue = array_merge($queue, Arr::wrap($allBlocks[$id]['children'] ?? []));
        }

        return $blocks;
    }
}

<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use Illuminate\Filesystem\Filesystem;

class ThemePersister
{
    public function __construct(
        protected Filesystem $files,
        protected ThemeDataCollector $themeDataCollector
    ) {}

    public function persist(array $data)
    {
        $this->persistTemplate($data);
        $this->persistThemeData($data);

        return redirect($data['url']);
    }

    protected function persistTemplate(array $data)
    {
        $path = $data['hasStaticContent']
            ? $this->getEditorContentPath($data['theme'], $data['channel'], $data['locale'], $data['template'])
            : $this->getEditorTemplatePath($data['theme'], $data['channel'], $data['locale'], $data['template']);

        $content = [
            'order' => $data['sectionsOrder'],
            'sections' => collect($data['sectionsOrder'])
                ->mapWithKeys(fn ($id) => [$id => $data['sectionsData'][$id]])
                ->all(),
        ];

        // Resolve parent data path and merge if applicable
        $parentDataPath = null;

        if ($data['templateDataPath'] !== $path && str_starts_with($data['templateDataPath'], config('bagisto_visual.data_path'))) {
            $parentDataPath = $data['templateDataPath'];
        } elseif ($this->files->exists($path)) {
            $currentData = json_decode($this->files->get($path), true);
            $parentDataPath = $currentData['parent'] ?? null;
        }

        if ($parentDataPath) {
            $parentData = $this->themeDataCollector->loadFileContent($parentDataPath);
            $content['parent'] = str_replace(config('bagisto_visual.data_path').DIRECTORY_SEPARATOR, '', $parentDataPath); // Store the relative path
            $content = $this->computeDiff($content, $parentData);
        }

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, json_encode($content));
    }

    protected function persistThemeData(array $data)
    {
        $content = [
            'settings' => $data['settings'],
            'sections' => collect(array_merge($data['beforeContentSectionsOrder'], $data['afterContentSectionsOrder']))
                ->mapWithKeys(fn ($id) => [$id => $data['sectionsData'][$id]])
                ->all(),
        ];

        $path = ThemePathsResolver::resolvePath(
            themeCode: $data['theme'],
            channel: $data['channel'],
            locale: $data['locale'],
            mode: 'editor',
            path: 'theme.json'
        );

        $parentDataPath = null;

        if ($this->files->exists($path)) {
            $currentData = json_decode($this->files->get($path), true);
            $parentDataPath = $currentData['parent'] ?? null;
        }

        if (! $parentDataPath) {
            $parentDataPath = ThemePathsResolver::resolveThemeFallbackDataPath(
                themeCode: $data['theme'],
                channel: $data['channel'],
                locale: $data['locale'],
                mode: 'editor'
            );
        }

        if ($parentDataPath && $parentDataPath !== $path) {
            $parentData = $this->themeDataCollector->loadFileContent($parentDataPath);
            $content['parent'] = str_replace(config('bagisto_visual.data_path').DIRECTORY_SEPARATOR, '', $parentDataPath); // Store the relative path
            $content = $this->computeDiff($content, $parentData);
        }

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, json_encode($content));
    }

    /**
     * Compute the differences between the current template data and its parent data.
     *
     * Special treatment is applied to the 'order' and 'blocks_order' keys.
     * If these keys differ, their values will be included in the diff.
     *
     * @param  array  $current  The current template data.
     * @param  array  $parent  The parent template data.
     * @return array An associative array of differences.
     */
    protected function computeDiff($current, $parent)
    {
        $diff = [];

        foreach ($current as $key => $value) {
            if (! array_key_exists($key, $parent)) {
                $diff[$key] = $value;

                continue;
            }

            if (($key === 'order' || $key === 'blocks_order') && $value !== $parent[$key]) {
                $diff[$key] = $value;

                continue;
            }

            if (is_array($value) && count($value) > 0) {
                $valueDiff = $this->computeDiff($value, $parent[$key]);
                if (count($valueDiff) > 0) {
                    $diff[$key] = $valueDiff;
                }

                continue;
            }

            if ($value !== $parent[$key]) {
                $diff[$key] = $value;
            }
        }

        return $diff;
    }

    protected function getEditorContentPath(string $theme, string $channel, string $locale, string $template)
    {
        return ThemePathsResolver::resolvePath(
            themeCode: $theme,
            channel: $channel,
            locale: $locale,
            mode: 'editor',
            path: "templates/presets/$template.json"
        );
    }

    protected function getEditorTemplatePath(string $theme, string $channel, string $locale, string $template)
    {
        return ThemePathsResolver::resolvePath(
            themeCode: $theme,
            channel: $channel,
            locale: $locale,
            mode: 'editor',
            path: "templates/$template.json"
        );
    }
}

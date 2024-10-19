<?php

namespace BagistoPlus\Visual;

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

        if (! empty($data['templateParent'])) {
            $parentDataPath = config('bagisto_visual.data_path').DIRECTORY_SEPARATOR.$data['templateParent'];
        } elseif ($data['dataPath'] !== $path && str_starts_with($data['dataPath'], config('bagisto_visual.data_path'))) {
            $parentDataPath = $data['dataPath'];
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
            'sections' => [],
        ];

        foreach (array_merge($data['beforeContentSectionsOrder'], $data['afterContentSectionsOrder']) as $id) {
            $content['sections'][$id] = $data['sectionsData'][$id];
        }

        $path = $this->getEditorThemeDataPath($data['theme'], $data['channel'], $data['locale']);

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

    protected function getEditorThemeDataPath(string $theme, string $channel, string $locale)
    {
        return strtr(
            '%data_path/themes/%theme_code/%mode/%channel/%locale/theme.json',
            [
                '%data_path' => rtrim(config('bagisto_visual.data_path'), '/\\'),
                '%theme_code' => $theme,
                '%channel' => $channel,
                '%locale' => $locale,
                '%mode' => 'editor',
            ]
        );
    }

    protected function getEditorContentPath(string $theme, string $channel, string $locale, string $template)
    {
        return strtr(
            '%data_path/themes/%theme_code/%mode/%channel/%locale/templates/presets/%template.json',
            [
                '%data_path' => rtrim(config('bagisto_visual.data_path'), '/\\'),
                '%theme_code' => $theme,
                '%channel' => $channel,
                '%locale' => $locale,
                '%mode' => 'editor',
                '%template' => $template,
            ]
        );
    }

    protected function getEditorTemplatePath(string $theme, string $channel, string $locale, string $template)
    {
        return strtr(
            '%data_path/themes/%theme_code/%mode/%channel/%locale/templates/%template.json',
            [
                '%data_path' => rtrim(config('bagisto_visual.data_path'), '/\\'),
                '%theme_code' => $theme,
                '%channel' => $channel,
                '%locale' => $locale,
                '%mode' => 'editor',
                '%template' => $template,
            ]
        );
    }
}

<?php

namespace BagistoPlus\Visual;

use Illuminate\Filesystem\Filesystem;

class ThemePersister
{
    public function __construct(protected Filesystem $files) {}

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
            'sections' => [],
            'order' => $data['sectionsOrder'],
        ];

        foreach ($data['sectionsOrder'] as $id) {
            $content['sections'][$id] = $data['sectionsData'][$id];
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

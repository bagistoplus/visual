<?php

namespace BagistoPlus\Visual\View;

class BladeDirectives
{
    public static function viewInfo()
    {
        $path = app('blade.compiler')->getPath();
        $filename = basename($path);
        $folder = basename(dirname($path));
        [$viewName] = explode('.', $filename);

        return [
            'type' => $folder,
            'view' => $viewName,
            'path' => $path,
            'name' => $folder.'/'.$viewName,
        ];
    }

    public static function visualLayoutContent(): string
    {
        $viewInfo = self::viewInfo();

        if ($viewInfo['type'] !== 'layouts') {
            throw new \Exception('You should use @visual_layout_content only inside layout views');
        }

        $themeEditorBefore = <<<PHP
        <?php
        if (ThemeEditor::inDesignMode()) {
            ThemeEditor::renderingView("{$viewInfo['name']}");
            ThemeEditor::startRenderingTemplate();
        }
        ?>
        PHP;

        $themeEditorAfter = <<<'PHP'
        <?php
        if (ThemeEditor::inDesignMode()) {
            ThemeEditor::stopRenderingTemplate();
        }
        ?>
        PHP;

        return $themeEditorBefore.
            '<?php echo $__env->yieldContent("layout_content"); ?>'
            .$themeEditorAfter;
    }

    public static function visualContent()
    {
        return '<?php $__env->startSection(\'layout_content\'); ?>';
    }

    public static function endVisualContent()
    {
        return '<?php $__env->stopSection(); ?>';
    }
}

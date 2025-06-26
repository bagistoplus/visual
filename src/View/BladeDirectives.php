<?php

namespace BagistoPlus\Visual\View;

/**
 * Retrieves view information based on the current compiled blade view file.
 *
 * @return array{type: string, view: string, path: string, name: string}
 */
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
        <!--BEGIN: template-->
        PHP;

        $themeEditorAfter = <<<'PHP'
        <!--END: template-->
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

    public static function visualDesignMode()
    {
        return '<?php if (ThemeEditor::inDesignMode()): ?>';
    }

    public static function endVisualDesignMode()
    {
        return '<?php endif; ?>';
    }

    public static function style(): string
    {
        return '<?php ob_start(); ?>';
    }

    public static function endStyle(): string
    {
        return <<<'PHP'
            <?php
                $css = ob_get_clean();

                if (app()->environment('production')) {
                    $css = preg_replace('/\s+/', ' ', $css);
                    $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
                    $css = trim($css);
                }

                echo "<style>{$css}</style>";
            ?>
        PHP;
    }

    public static function visualColorVars($expression)
    {
        return "<?php echo \BagistoPlus\Visual\View\BladeDirectives::generateColorPalette($expression); ?>";
    }

    public static function generateColorPalette($name, $color)
    {
        $shades = TailwindPaletteGenerator::generate($color->toRgb());

        $palette = '';

        foreach ($shades as $key => $c) {
            $palette .= "--color-{$name}-{$key}: {$c->red} {$c->green} {$c->blue};\n";
        }

        return $palette;
    }
}

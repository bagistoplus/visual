<?php

namespace BagistoPlus\Visual\View;

class BladeDirectives
{
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

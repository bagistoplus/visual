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

    /**
     * Mark a part of the page that the full page cache has to rebuild per
     * visitor. Register the matching resolver with Visual::registerFpcRegion().
     */
    public static function fpcRegion($expression): string
    {
        return "<?php app(\BagistoPlus\Visual\Support\FpcRegion::class)->begin($expression); ?>";
    }

    public static function endFpcRegion(): string
    {
        return '<?php echo app(\BagistoPlus\Visual\Support\FpcRegion::class)->end(); ?>';
    }

    /**
     * The same, for a region a page needs once however many blocks ask for it.
     *
     * The condition is compiled in front of the body rather than around its
     * result, so a later caller does not run the body at all. Blade's own
     * `@once` cannot stand in: the view factory forgets what it has rendered
     * once each time the render stack empties, and a block is its own top level
     * render.
     */
    public static function fpcRegionOnce($expression): string
    {
        return "<?php if (app(\BagistoPlus\Visual\Support\FpcRegion::class)->beginOnce($expression)): ?>";
    }

    public static function endFpcRegionOnce(): string
    {
        return '<?php echo app(\BagistoPlus\Visual\Support\FpcRegion::class)->end(); endif; ?>';
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

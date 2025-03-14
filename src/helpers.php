<?php

if (! function_exists('visual_clear_inline_styles')) {
    function visual_clear_inline_styles($html)
    {
        return preg_replace('#(<[a-z0-6 ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $html);
    }
}

if (! function_exists('visual_mapped_svg_icon')) {
    function visual_mapped_svg_icon(string $icon): string
    {
        return config("bagisto_visual_iconmap.$icon", 'lucide-file-question');
    }
}

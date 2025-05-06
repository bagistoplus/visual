<?php

if (! function_exists('visual_clear_inline_styles')) {
    function visual_clear_inline_styles($html)
    {
        return preg_replace('#(<[a-z0-6 ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $html);
    }
}

if (! function_exists('visual_is_menu_active')) {
    function visual_is_menu_active($menu)
    {
        $menuUrl = str($menu->getUrl())->before('?');

        return str(request()->url())->is($menuUrl.'*');
    }
}

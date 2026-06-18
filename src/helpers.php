<?php

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Support\TemplateAssignment;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Theme\Theme as VisualTheme;
use Craftile\Core\Data\ResponsiveValue;
use Illuminate\Database\Eloquent\Model;

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

if (! function_exists('visual_is_responsive_value')) {
    /**
     * Check if a value is a ResponsiveValue instance.
     */
    function visual_is_responsive_value(mixed $value): bool
    {
        return $value instanceof ResponsiveValue;
    }
}

if (! function_exists('visual_template_for')) {
    function visual_template_for(string $type, ?Model $model = null): string
    {
        if (! in_array($type, TemplateDiscovery::ASSIGNABLE_TYPES, true)) {
            return $type;
        }

        $channel = core()->getRequestedChannelCode();
        $locale = core()->getRequestedLocaleCode();
        $theme = themes()->current();

        if (! $theme instanceof VisualTheme || ! $theme->isVisualTheme()) {
            return $type;
        }

        $templates = app(TemplateDiscovery::class);

        if (
            ThemeEditor::inDesignMode()
            && ($requested = request()->query('_template'))
            && $templates->typeForKey($requested) === $type
            && $templates->exists($theme, $requested, $type, $channel, $locale, true)
        ) {
            return $requested;
        }

        if ($model && Visual::templateAssignmentsEnabled()) {
            return app(TemplateAssignment::class)->resolve(
                model: $model,
                type: $type,
                theme: $theme,
                channel: $channel,
                locale: $locale,
                includeEditorDrafts: ThemeEditor::inDesignMode(),
            );
        }

        return $type;
    }
}

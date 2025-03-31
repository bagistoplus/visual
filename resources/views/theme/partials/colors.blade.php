<style>
  :root {
    @visual_color_vars('primary', $theme->settings->light_primary_color ?? '#92400e')

    @visual_color_vars('secondary', $theme->settings->light_secondary_color ?? '#44403c')

    @visual_color_vars('accent', $theme->settings->light_accent_color ?? '#84cc16')

    @visual_color_vars('surface', $theme->settings->light_surface_color ?? '#ffffff')

    @visual_color_vars('surface-alt', $theme->settings->light_surface_alt_color ?? '#fafaf9')

    @visual_color_vars('danger', $theme->settings->light_danger_color ?? '#dc2626')

    @visual_color_vars('warning', $theme->settings->light_warning_color ?? '#d97706')

    @visual_color_vars('info', $theme->settings->light_info_color ?? '#2563eb')

    @visual_color_vars('success', $theme->settings->light_success_color ?? '#059669')

    @visual_color_vars('neutral', $theme->settings->light_neutral_color ?? '#71717a')

    @if ($bg = $theme->settings->light_background_color)
      --color-background: {{ $bg->toRgb()->red() }} {{ $bg->toRgb()->green() }} {{ $bg->toRgb()->blue() }};
    @else
      --color-background: 255 255 255;
    @endif
  }
</style>

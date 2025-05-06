<?php

namespace BagistoPlus\Visual\Commands;

use Illuminate\Console\Command;
use Spatie\Browsershot\Browsershot;

use function Laravel\Prompts\error;
use function Laravel\Prompts\select;

class GeneratePreviewCommand extends Command
{
    protected $signature = 'visual:generate-preview
                            {theme? : Theme code (e.g. awesome-theme)}';

    protected $description = 'Generate a preview screenshot for a theme homepage';

    public function handle(): void
    {
        $theme = $this->argument('theme');

        if (! $theme) {
            $theme = $this->selectTheme();
        }

        if (! $this->isVisualTheme($theme)) {
            error("❌ '{$theme}' is not a valid Bagisto Visual theme.");

            return;
        }

        $url = url('/?').http_build_query(['_previewMode' => $theme]);

        $themePath = config("themes.shop.{$theme}.base_path");
        $previewPath = "$themePath/".'/resources/assets/images';

        if (! is_dir($previewPath)) {
            mkdir($previewPath, 0755, true);
        }

        $filePath = "$previewPath/theme-preview.png";

        $this->info("Generating preview for: {$url}");

        try {
            Browsershot::url($url)
                ->windowSize(1024, 800)
                // ->clip(0, 0, 1200, 800)
                ->waitUntilNetworkIdle()
                ->setScreenshotType('png')
                ->setNodeModulePath(__DIR__.'/../../node_modules')
                ->save($filePath);

            info("✅ Preview image saved to: {$filePath}");
        } catch (\Exception $e) {
            error('❌ Failed to capture screenshot: '.$e->getMessage());
        }
    }

    protected function selectTheme(): string
    {
        $themes = collect(config('themes.shop', []))
            ->filter(fn ($theme) => ($theme['visual_theme'] ?? false) === true)
            ->mapWithKeys(fn ($theme, $key) => [
                $key => $theme['name'] ?? $key,
            ])
            ->all();

        return select('Select a Visual theme', $themes);
    }

    protected function isVisualTheme(string $theme): bool
    {
        $shopThemes = config('themes.shop', []);

        return isset($shopThemes[$theme]['visual_theme']) && $shopThemes[$theme]['visual_theme'] === true;
    }
}

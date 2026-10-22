<?php

namespace BagistoPlus\Visual\Commands;

use BagistoPlus\Visual\Support\ThemePreviewComposer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class GeneratePreviewCommand extends Command
{
    protected $signature = 'visual:generate-preview
                            {theme? : Theme code (e.g. awesome-theme)}
                            {--plain : Save a single desktop screenshot instead of the desktop and phone composite}';

    protected $description = 'Generate a preview image for a theme homepage';

    protected const MOBILE_USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

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
            if ($this->option('plain')) {
                $this->capture($url, 1280, 720)->save($filePath);
            } else {
                $this->composite($url, $filePath);
            }

            info("✅ Preview image saved to: {$filePath}");
        } catch (\Exception $e) {
            error('❌ Failed to capture screenshot: '.$e->getMessage());
        }
    }

    /**
     * Capture the storefront on a desktop and on a phone, and lay both into
     * one image.
     */
    protected function composite(string $url, string $filePath): void
    {
        $directory = storage_path('framework/cache/visual-preview-'.uniqid());
        File::ensureDirectoryExists($directory);

        try {
            $this->capture($url, 1440, 810)
                ->deviceScaleFactor(2)
                ->save("$directory/desktop.png");

            $this->capture($url, 390, 844)
                ->deviceScaleFactor(2)
                ->mobile()
                ->touch()
                ->userAgent(self::MOBILE_USER_AGENT)
                ->save("$directory/mobile.png");

            app(ThemePreviewComposer::class)->compose(
                "$directory/desktop.png",
                "$directory/mobile.png",
                $filePath
            );
        } finally {
            File::deleteDirectory($directory);
        }
    }

    /**
     * A capture of the storefront once its fonts have loaded, with Laravel
     * Debugbar, which development stores draw on every page, kept out of it.
     */
    protected function capture(string $url, int $width, int $height): Browsershot
    {
        return Browsershot::url($url)
            ->windowSize($width, $height)
            ->waitUntilNetworkIdle()
            ->waitForFunction('document.fonts.status === "loaded"')
            ->setOption('addStyleTag', json_encode(['content' => '.phpdebugbar { display: none !important; }']))
            ->setScreenshotType('png')
            ->setNodeModulePath(__DIR__.'/../../node_modules');
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

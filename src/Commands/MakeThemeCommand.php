<?php

namespace BagistoPlus\Visual\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

class MakeThemeCommand extends Command
{
    protected $signature = 'visual:make-theme {name?} {--vendor=Themes} {--author=}';

    protected $description = 'Create a new Bagisto Visual theme structure';

    public function handle()
    {
        $name = $this->argument('name') ?? text('🎨 What is the theme name?');
        $vendor = $this->option('vendor') ?? text('🏷️ What is the vendor namespace?', 'Themes');
        $author = $this->option('author') ?: text('✍️ Who is the theme author?', 'Your Company Name');

        $name = Str::studly($name);
        $vendor = Str::studly($vendor);
        $title = Str::headline($name);
        $slug = Str::kebab($name);
        $vendorSlug = Str::kebab($vendor);

        $basePath = base_path("packages/{$vendor}/{$name}");
        $vars = [
            'name' => $name,
            'title' => $title,
            'slug' => $slug,
            'vendor' => $vendor,
            'vendor_slug' => $vendorSlug,
            'namespace' => "{$vendor}\\{$name}",
            'author' => $author,
        ];

        if (File::exists($basePath)) {
            $this->error('Theme already exists!');

            return 1;
        }

        // Create base directories
        File::makeDirectory("{$basePath}/resources/views/layouts", 0755, true);
        File::makeDirectory("{$basePath}/resources/views/components", 0755, true);
        File::makeDirectory("{$basePath}/resources/views/templates", 0755, true);
        File::makeDirectory("{$basePath}/resources/views/sections", 0755, true);
        File::makeDirectory("{$basePath}/resources/views/blocks", 0755, true);
        File::makeDirectory("{$basePath}/resources/assets/images", 0755, true);
        File::makeDirectory("{$basePath}/resources/assets/css", 0755, true);
        File::makeDirectory("{$basePath}/resources/assets/js", 0755, true);
        File::makeDirectory("{$basePath}/config", 0755, true);
        File::makeDirectory("{$basePath}/src/Sections", 0755, true);
        File::makeDirectory("{$basePath}/src/Blocks", 0755, true);
        File::makeDirectory("{$basePath}/public/themes/shop/{$slug}", 0755, true);

        // Files to generate from stubs
        $files = [
            'theme.php' => 'config/theme.php',
            'settings.php' => 'config/settings.php',
            'README.md' => 'README.md',
            'default.blade.php' => 'resources/views/layouts/default.blade.php',
            'colors.blade.php' => 'resources/views/partials/colors.blade.php',
            'composer.json' => 'composer.json',
            'package.json' => 'package.json',
            'vite.config.ts' => 'vite.config.ts',
            'theme.css' => 'resources/assets/css/theme.css',
            'theme.js' => 'resources/assets/js/theme.js',
            'ServiceProvider.php' => 'src/ServiceProvider.php',
            '.gitignore' => '.gitignore',
            '.gitkeep' => "public/themes/shop/{$slug}/.gitkeep",
        ];

        foreach ($files as $stub => $target) {
            $stubPath = __DIR__."/../../stubs/theme/{$stub}.stub";
            $targetPath = "{$basePath}/{$target}";

            File::ensureDirectoryExists(dirname($targetPath));
            $stubContent = File::get($stubPath);
            $rendered = $this->replaceStubVars($stubContent, $vars);
            File::put($targetPath, $rendered);
        }

        File::copy(__DIR__.'/../../stubs/theme/theme-preview.png.stub', "{$basePath}/resources/assets/images/theme-preview.png");

        $this->ensureComposerPathRepository();

        info("✅ Theme '{$name}' created successfully at: packages/{$vendor}/{$name}");

        return 0;
    }

    protected function replaceStubVars(string $stub, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $stub = str_replace('{{ '.$key.' }}', $value, $stub);
        }

        return $stub;
    }

    protected function ensureComposerPathRepository(): void
    {
        $composerFile = base_path('composer.json');
        $composer = json_decode(file_get_contents($composerFile), true);

        $expectedRepo = [
            'type' => 'path',
            'url' => 'packages/*/*',
            'options' => ['symlink' => true],
        ];

        $repositories = $composer['repositories'] ?? [];

        $alreadyExists = collect($repositories)->contains(function ($repo) use ($expectedRepo) {
            return $repo['type'] === $expectedRepo['type']
                && $repo['url'] === $expectedRepo['url'];
        });

        if (! $alreadyExists) {
            $composer['repositories'][] = $expectedRepo;

            file_put_contents(
                $composerFile,
                json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            info('✅ Added path repository to composer.json');
        }
    }
}

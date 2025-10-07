<?php

namespace BagistoPlus\Visual\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeBlockCommand extends Command
{
    protected $signature = 'visual:make-block
                            {name? : The name of the block}
                            {--theme= : The theme slug (optional)}
                            {--component : Create a Blade component-based block}
                            {--force : Overwrite existing block if it exists}';

    protected $description = 'Create a new block for a Bagisto Visual theme';

    public function handle(): int
    {
        $name = $this->argument('name') ?? text('🧱 Block name (e.g., ProductCard)');
        $slug = Str::kebab($name);
        $class = Str::studly($name);
        $component = $this->option('component');
        $force = $this->option('force');

        // Resolve theme
        $theme = $this->option('theme');

        if (! $theme) {
            $themes = collect(config('themes.shop', []))
                ->filter(fn ($config) => $config['visual_theme'] ?? false)
                ->mapWithKeys(fn ($config, $code) => [$code => $config['name'] ?? $code]);

            if ($themes->isNotEmpty()) {
                $theme = select(
                    label: '🎨 Select the target theme (leave blank to use app/Visual)',
                    options: array_merge($themes->toArray(), [
                        '__app' => 'In default app',
                    ]),
                    default: $themes->keys()->first()
                );
            }
        }

        $generateInApp = $theme === '__app';
        $namespace = '';
        $classPath = '';
        $viewPath = '';

        if ($generateInApp) {
            $namespace = 'App\\Visual\\Blocks';
            $classPath = base_path("app/Visual/Blocks/{$class}.php");
            $viewPath = resource_path("views/blocks/{$slug}.blade.php");
        } else {
            // Resolve theme path and namespace
            $themeConfig = config("themes.shop.$theme");

            if (! $themeConfig || ! isset($themeConfig['base_path'])) {
                error("❌ Could not locate base_path for theme [$theme]");

                return 1;
            }

            $themePath = $themeConfig['base_path'];
            $composerPath = $themePath.'/composer.json';

            if (! File::exists($composerPath)) {
                $this->error("❌ composer.json not found in theme path: $composerPath");

                return 1;
            }

            $composer = json_decode(File::get($composerPath), true);

            $namespace = collect($composer['autoload']['psr-4'] ?? [])
                ->filter(fn ($path) => Str::of($themePath.'/'.$path)->finish('/')->__toString() === $themePath.'/src/')
                ->keys()
                ->first();

            if (! $namespace) {
                error('❌ Could not infer PSR-4 namespace from composer.json');

                return 1;
            }

            $namespace = rtrim($namespace, '\\').'\\Blocks';
            $classPath = "{$themePath}/src/Blocks/{$class}.php";
            $viewPath = "{$themePath}/resources/views/blocks/{$slug}.blade.php";
        }

        if (File::exists($classPath) && ! $force) {
            error("❌ Block class already exists: {$classPath} (use --force to overwrite)");

            return 1;
        }

        if (File::exists($viewPath) && ! $force) {
            error("❌ Blade view already exists: {$viewPath} (use --force to overwrite)");

            return 1;
        }

        $vars = [
            'class' => $class,
            'slug' => $slug,
            'view' => "shop::blocks.{$slug}",
            'namespace' => $namespace,
            'theme' => $theme ?? 'app',
        ];

        $classStub = $component ? 'BladeBlock.php' : 'SimpleBlock.php';

        $files = [
            $classStub => $classPath,
            'block.blade.php' => $viewPath,
        ];

        foreach ($files as $stub => $targetPath) {
            $stubPath = __DIR__."/../../stubs/block/{$stub}.stub";

            File::ensureDirectoryExists(dirname($targetPath));
            $stubContent = File::get($stubPath);
            $rendered = $this->replaceStubVars($stubContent, $vars);
            File::put($targetPath, $rendered);
        }

        $this->info(" Created {$classPath}");
        $this->info(" Created {$viewPath}");
        info("✅ Block '{$class}' created successfully in ".($generateInApp ? 'app/Visual' : "theme '{$theme}'"));

        return 0;
    }

    protected function replaceStubVars(string $stub, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $stub = str_replace('{{ '.$key.' }}', $value, $stub);
        }

        return $stub;
    }
}

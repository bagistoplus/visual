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
                            {--livewire : Create a Livewire component-based block}
                            {--force : Overwrite existing block if it exists}';

    protected $description = 'Create a new block for a Bagisto Visual theme';

    public function handle(): int
    {
        $name = $this->argument('name') ?? text('🧱 Block name (e.g., ProductCard or Carousel/Slide)');

        $parts = explode('/', $name);
        $className = Str::studly(array_pop($parts));
        $folderParts = array_map(fn ($part) => Str::studly($part), $parts);
        $folderPath = $folderParts ? implode('/', $folderParts) : '';
        $folderNamespace = $folderParts ? '\\'.implode('\\', $folderParts) : '';

        $slugParts = array_map(fn ($part) => Str::kebab($part), explode('/', $name));
        $slug = implode('/', $slugParts);
        $viewSlug = str_replace('/', '.', $slug);

        $component = $this->option('component');
        $livewire = $this->option('livewire');
        $force = $this->option('force');

        if ($component && $livewire) {
            error('❌ Cannot use both --component and --livewire flags');

            return 1;
        }

        $theme = $this->option('theme');

        if (! $theme) {
            $themes = collect(config('themes.shop', []))
                ->filter(fn ($config) => $config['visual_theme'] ?? false)
                ->mapWithKeys(fn ($config, $code) => [$code => $config['name'] ?? $code]);

            if ($themes->isNotEmpty()) {
                $theme = select(
                    label: '🎨 Select the target theme (\'In default app\' to use app/Visual)',
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
            $namespace = 'App\\Visual\\Blocks'.$folderNamespace;
            $classPath = base_path("app/Visual/Blocks/{$folderPath}".($folderPath ? '/' : '')."{$className}.php");
            $viewPath = resource_path("views/blocks/{$slug}.blade.php");
        } else {
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

            $namespace = rtrim($namespace, '\\').'\\Blocks'.$folderNamespace;
            $classPath = "{$themePath}/src/Blocks/{$folderPath}".($folderPath ? '/' : '')."{$className}.php";
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
            'class' => $className,
            'slug' => $slug,
            'view' => $generateInApp ? "blocks.{$viewSlug}" : "shop::blocks.{$viewSlug}",
            'namespace' => $namespace,
            'theme' => $theme ?? 'app',
        ];

        $classStub = $livewire ? 'LivewireBlock.php' : ($component ? 'BladeBlock.php' : 'SimpleBlock.php');

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
        info("✅ Block '{$className}' created successfully in ".($generateInApp ? 'app/Visual' : "theme '{$theme}'"));

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

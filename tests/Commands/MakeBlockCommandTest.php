<?php

use BagistoPlus\Visual\Commands\MakeBlockCommand;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->testAppPath = base_path('app/Visual/Blocks');
    $this->testViewPath = resource_path('views/blocks');
});

afterEach(function () {
    // Clean up test files
    if (File::exists($this->testAppPath)) {
        File::deleteDirectory(dirname($this->testAppPath));
    }
    if (File::exists($this->testViewPath)) {
        File::deleteDirectory($this->testViewPath);
    }
});

it('creates a simple block by default', function () {
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'ProductCard',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Blocks/ProductCard.php');
    $viewPath = resource_path('views/blocks/product-card.blade.php');

    expect(File::exists($classPath))->toBeTrue();
    expect(File::exists($viewPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('namespace App\Visual\Blocks');
    expect($content)->toContain('use BagistoPlus\Visual\Blocks\SimpleBlock');
    expect($content)->toContain('class ProductCard extends SimpleBlock');
});

it('creates a blade component block with --component flag', function () {
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'ProductCard',
        '--theme' => '__app',
        '--component' => true,
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Blocks/ProductCard.php');

    expect(File::exists($classPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('use BagistoPlus\Visual\Blocks\BladeBlock');
    expect($content)->toContain('class ProductCard extends BladeBlock');
    expect($content)->toContain('public static function blocks(): array');
});

it('fails when block already exists without --force', function () {
    // Create block first time
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'ProductCard',
        '--theme' => '__app',
    ])->assertExitCode(0);

    // Try to create again without --force
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'ProductCard',
        '--theme' => '__app',
    ])->assertExitCode(1);
});

it('overwrites block with --force flag', function () {
    // Create block first time
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'ProductCard',
        '--theme' => '__app',
    ])->assertExitCode(0);

    // Overwrite with --force
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'ProductCard',
        '--theme' => '__app',
        '--force' => true,
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Blocks/ProductCard.php');
    expect(File::exists($classPath))->toBeTrue();
});

it('converts block name to proper case formats', function () {
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'product-card',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Blocks/ProductCard.php');
    $viewPath = resource_path('views/blocks/product-card.blade.php');

    expect(File::exists($classPath))->toBeTrue();
    expect(File::exists($viewPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('class ProductCard extends SimpleBlock');
});

it('creates nested block with subfolder support', function () {
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'Carousel/Slide',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Blocks/Carousel/Slide.php');
    $viewPath = resource_path('views/blocks/carousel/slide.blade.php');

    expect(File::exists($classPath))->toBeTrue();
    expect(File::exists($viewPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('namespace App\Visual\Blocks\Carousel');
    expect($content)->toContain('class Slide extends SimpleBlock');
});

it('creates deeply nested block with multiple subfolders', function () {
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'Components/Carousel/Slide',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Blocks/Components/Carousel/Slide.php');
    $viewPath = resource_path('views/blocks/components/carousel/slide.blade.php');

    expect(File::exists($classPath))->toBeTrue();
    expect(File::exists($viewPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('namespace App\Visual\Blocks\Components\Carousel');
    expect($content)->toContain('class Slide extends SimpleBlock');
});

it('generates correct view reference for nested blocks', function () {
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'Carousel/Slide',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Blocks/Carousel/Slide.php');
    $content = File::get($classPath);

    expect($content)->toContain("'blocks.carousel.slide'");
    expect($content)->not->toContain("'shop::blocks");
});

it('generates view with editor attributes', function () {
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'TestBlock',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $viewPath = resource_path('views/blocks/test-block.blade.php');
    $content = File::get($viewPath);

    expect($content)->toContain('{{ $block->editor_attributes }}');
});

it('handles kebab-case in nested block names', function () {
    $this->artisan(MakeBlockCommand::class, [
        'name' => 'ProductCarousel/FeaturedSlide',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Blocks/ProductCarousel/FeaturedSlide.php');
    $viewPath = resource_path('views/blocks/product-carousel/featured-slide.blade.php');

    expect(File::exists($classPath))->toBeTrue();
    expect(File::exists($viewPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('namespace App\Visual\Blocks\ProductCarousel');
    expect($content)->toContain('class FeaturedSlide extends SimpleBlock');
    expect($content)->toContain("'blocks.product-carousel.featured-slide'");
});

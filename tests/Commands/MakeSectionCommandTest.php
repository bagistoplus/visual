<?php

use BagistoPlus\Visual\Commands\MakeSectionCommand;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->testAppPath = base_path('app/Visual/Sections');
    $this->testViewPath = resource_path('views/sections');
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

it('creates a simple section by default', function () {
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'TestSection',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Sections/TestSection.php');
    $viewPath = resource_path('views/sections/test-section.blade.php');

    expect(File::exists($classPath))->toBeTrue();
    expect(File::exists($viewPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('namespace App\Visual\Sections');
    expect($content)->toContain('use BagistoPlus\Visual\Blocks\SimpleSection');
    expect($content)->toContain('class TestSection extends SimpleSection');
});

it('creates a blade component section with --component flag', function () {
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'TestSection',
        '--theme' => '__app',
        '--component' => true,
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Sections/TestSection.php');

    expect(File::exists($classPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('use BagistoPlus\Visual\Blocks\BladeSection');
    expect($content)->toContain('class TestSection extends BladeSection');
    expect($content)->toContain('public static function settings(): array');
});

it('fails when section already exists without --force', function () {
    // Create section first time
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'TestSection',
        '--theme' => '__app',
    ])->assertExitCode(0);

    // Try to create again without --force
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'TestSection',
        '--theme' => '__app',
    ])->assertExitCode(1);
});

it('overwrites section with --force flag', function () {
    // Create section first time
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'TestSection',
        '--theme' => '__app',
    ])->assertExitCode(0);

    // Overwrite with --force
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'TestSection',
        '--theme' => '__app',
        '--force' => true,
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Sections/TestSection.php');
    expect(File::exists($classPath))->toBeTrue();
});

it('converts section name to proper case formats', function () {
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'announcement-bar',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Sections/AnnouncementBar.php');
    $viewPath = resource_path('views/sections/announcement-bar.blade.php');

    expect(File::exists($classPath))->toBeTrue();
    expect(File::exists($viewPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('class AnnouncementBar extends SimpleSection');
});

it('creates nested section with subfolder support', function () {
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'Hero/Banner',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Sections/Hero/Banner.php');
    $viewPath = resource_path('views/sections/hero/banner.blade.php');

    expect(File::exists($classPath))->toBeTrue();
    expect(File::exists($viewPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('namespace App\Visual\Sections\Hero');
    expect($content)->toContain('class Banner extends SimpleSection');
});

it('creates deeply nested section with multiple subfolders', function () {
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'Components/Hero/Banner',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Sections/Components/Hero/Banner.php');
    $viewPath = resource_path('views/sections/components/hero/banner.blade.php');

    expect(File::exists($classPath))->toBeTrue();
    expect(File::exists($viewPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('namespace App\Visual\Sections\Components\Hero');
    expect($content)->toContain('class Banner extends SimpleSection');
});

it('generates correct view reference for nested sections', function () {
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'Hero/Banner',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Sections/Hero/Banner.php');
    $content = File::get($classPath);

    expect($content)->toContain("'sections.hero.banner'");
    expect($content)->not->toContain("'shop::sections");
});

it('generates view with editor attributes', function () {
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'TestSection',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $viewPath = resource_path('views/sections/test-section.blade.php');
    $content = File::get($viewPath);

    expect($content)->toContain('{{ $section->editor_attributes }}');
});

it('handles kebab-case in nested section names', function () {
    $this->artisan(MakeSectionCommand::class, [
        'name' => 'FeaturedHero/MainBanner',
        '--theme' => '__app',
    ])->assertExitCode(0);

    $classPath = base_path('app/Visual/Sections/FeaturedHero/MainBanner.php');
    $viewPath = resource_path('views/sections/featured-hero/main-banner.blade.php');

    expect(File::exists($classPath))->toBeTrue();
    expect(File::exists($viewPath))->toBeTrue();

    $content = File::get($classPath);
    expect($content)->toContain('namespace App\Visual\Sections\FeaturedHero');
    expect($content)->toContain('class MainBanner extends SimpleSection');
    expect($content)->toContain("'sections.featured-hero.main-banner'");
});

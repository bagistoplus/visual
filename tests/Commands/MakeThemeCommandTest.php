<?php

use BagistoPlus\Visual\Commands\MakeThemeCommand;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->testPackagesPath = base_path('packages');
});

afterEach(function () {
    if (File::exists($this->testPackagesPath)) {
        File::deleteDirectory($this->testPackagesPath);
    }
});

it('creates theme with all required files and directories', function () {
    $this->artisan(MakeThemeCommand::class, [
        'name' => 'TestTheme',
        '--vendor' => 'TestVendor',
        '--author' => 'Test Author',
    ])->assertExitCode(0);

    $themePath = base_path('packages/TestVendor/TestTheme');

    expect(File::exists($themePath))->toBeTrue();
    expect(File::exists("{$themePath}/src/ServiceProvider.php"))->toBeTrue();
    expect(File::exists("{$themePath}/config/theme.php"))->toBeTrue();
    expect(File::exists("{$themePath}/config/settings.php"))->toBeTrue();
    expect(File::exists("{$themePath}/composer.json"))->toBeTrue();
    expect(File::exists("{$themePath}/package.json"))->toBeTrue();
    expect(File::exists("{$themePath}/README.md"))->toBeTrue();
    expect(File::exists("{$themePath}/resources/views/layouts/default.blade.php"))->toBeTrue();
    expect(File::exists("{$themePath}/resources/assets/css/theme.css"))->toBeTrue();
    expect(File::exists("{$themePath}/resources/assets/js/theme.js"))->toBeTrue();
});

it('creates theme with correct namespace and class names', function () {
    $this->artisan(MakeThemeCommand::class, [
        'name' => 'TestTheme',
        '--vendor' => 'TestVendor',
        '--author' => 'Test Author',
    ])->assertExitCode(0);

    $themePath = base_path('packages/TestVendor/TestTheme');
    $serviceProviderContent = File::get("{$themePath}/src/ServiceProvider.php");

    expect($serviceProviderContent)->toContain('namespace TestVendor\TestTheme');
    expect($serviceProviderContent)->toContain('class ServiceProvider');
});

it('converts theme name to proper case formats', function () {
    $this->artisan(MakeThemeCommand::class, [
        'name' => 'my-awesome-theme',
        '--vendor' => 'cool-vendor',
        '--author' => 'Test Author',
    ])->assertExitCode(0);

    $themePath = base_path('packages/CoolVendor/MyAwesomeTheme');

    expect(File::exists($themePath))->toBeTrue();

    $composerContent = File::get("{$themePath}/composer.json");
    expect($composerContent)->toContain('"name": "cool-vendor/my-awesome-theme"');
});

it('uses default vendor when not specified', function () {
    $this->artisan(MakeThemeCommand::class, [
        'name' => 'TestTheme',
        '--author' => 'Test Author',
    ])->assertExitCode(0);

    $themePath = base_path('packages/Themes/TestTheme');

    expect(File::exists($themePath))->toBeTrue();
});

it('fails when theme already exists', function () {
    $this->artisan(MakeThemeCommand::class, [
        'name' => 'TestTheme',
        '--vendor' => 'TestVendor',
        '--author' => 'Test Author',
    ])->assertExitCode(0);

    $this->artisan(MakeThemeCommand::class, [
        'name' => 'TestTheme',
        '--vendor' => 'TestVendor',
        '--author' => 'Test Author',
    ])->assertExitCode(1);
});

it('creates all required directories', function () {
    $this->artisan(MakeThemeCommand::class, [
        'name' => 'TestTheme',
        '--vendor' => 'TestVendor',
        '--author' => 'Test Author',
    ])->assertExitCode(0);

    $themePath = base_path('packages/TestVendor/TestTheme');

    expect(File::isDirectory("{$themePath}/resources/views/layouts"))->toBeTrue();
    expect(File::isDirectory("{$themePath}/resources/views/components"))->toBeTrue();
    expect(File::isDirectory("{$themePath}/resources/views/templates"))->toBeTrue();
    expect(File::isDirectory("{$themePath}/resources/views/sections"))->toBeTrue();
    expect(File::isDirectory("{$themePath}/resources/views/blocks"))->toBeTrue();
    expect(File::isDirectory("{$themePath}/resources/assets/images"))->toBeTrue();
    expect(File::isDirectory("{$themePath}/resources/assets/css"))->toBeTrue();
    expect(File::isDirectory("{$themePath}/resources/assets/js"))->toBeTrue();
    expect(File::isDirectory("{$themePath}/config"))->toBeTrue();
    expect(File::isDirectory("{$themePath}/src/Sections"))->toBeTrue();
    expect(File::isDirectory("{$themePath}/src/Blocks"))->toBeTrue();
});

it('generates composer.json with correct structure', function () {
    $this->artisan(MakeThemeCommand::class, [
        'name' => 'TestTheme',
        '--vendor' => 'TestVendor',
        '--author' => 'Test Author',
    ])->assertExitCode(0);

    $themePath = base_path('packages/TestVendor/TestTheme');
    $composerContent = File::get("{$themePath}/composer.json");
    $composer = json_decode($composerContent, true);

    expect($composer['name'])->toBe('test-vendor/test-theme');
    expect($composer['type'])->toBe('library');
    expect($composer['autoload']['psr-4'])->toHaveKey('TestVendor\\TestTheme\\');
});

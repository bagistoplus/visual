<?php

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\View\JsonViewCompiler;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\View\Compilers\BladeCompiler;

it('compiles json template correctly', function () {
    $cachePath = sys_get_temp_dir().'/cache';
    $this->filesystem = new Filesystem;
    $this->bladeCompiler = new BladeCompiler($this->filesystem, $cachePath);

    // Create the JsonViewCompiler instance
    $this->compiler = new JsonViewCompiler($this->filesystem, $cachePath, $this->bladeCompiler);

    $path = sys_get_temp_dir().'/view.json';
    File::put($path, json_encode([
        'sections' => [
            'header' => ['type' => 'header'],
            'footer' => ['type' => 'footer'],
        ],
        'order' => ['header', 'footer'],
    ]));

    // Simulate sections
    Sections::shouldReceive('get')
        ->with('header')
        ->andReturn(new class
        {
            public function renderToBlade()
            {
                return '<div>Header</div>';
            }
        });

    Sections::shouldReceive('get')
        ->with('footer')
        ->andReturn(new class
        {
            public function renderToBlade()
            {
                return '<div>Footer</div>';
            }
        });

    // Act
    $this->compiler->compile($path);

    // Assert
    $compiledPath = $this->compiler->getCompiledPath($path);

    expect(file_get_contents($compiledPath))->toContain('<div>Header</div>');
    expect(file_get_contents($compiledPath))->toContain('<div>Footer</div>');

    // Cleanup
    unlink($path);
    unlink($compiledPath);
});

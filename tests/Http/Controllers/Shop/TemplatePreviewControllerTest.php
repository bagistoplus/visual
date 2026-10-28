<?php

use BagistoPlus\Visual\Http\Controllers\Shop\TemplatePreviewController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
    });

    $this->views = sys_get_temp_dir().'/visual-template-preview-'.uniqid();
    File::ensureDirectoryExists("{$this->views}/checkout");
    File::put("{$this->views}/checkout/success.blade.php", '');
    view()->addNamespace('shop', $this->views);
});

afterEach(function () {
    File::deleteDirectory($this->views);
});

it('previews checkout success with products that use the store placeholder', function () {
    Storage::fake('public');

    $order = (new TemplatePreviewController)->checkoutSuccess()->getData()['order'];

    expect($order->items)->not->toBeEmpty();

    foreach ($order->items as $item) {
        expect($item->product->images)->toBeEmpty();
    }

    expect(Storage::disk('public')->allFiles())->toBe([]);
});

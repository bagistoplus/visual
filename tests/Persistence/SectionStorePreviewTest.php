<?php

use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Support\SectionOptions;
use Illuminate\Http\Request;

function previewingStore(): SectionStore
{
    return new class(new SectionOptions) extends SectionStore
    {
        protected function repository(): mixed
        {
            return new class
            {
                public function getDraftedForPreview(int $channelId, string $themeCode, string $locale): array
                {
                    return [];
                }
            };
        }
    };
}

it('says this request is to be read as the editor left it', function () {
    $request = Request::create('/');
    app()->instance('request', $request);

    previewingStore()->previewDrafts();

    expect($request->attributes->get('appearance_previewing'))->toBeTrue();
});

it('reads the drafted sections without saying anything about the request', function () {
    $request = Request::create('/');
    app()->instance('request', $request);

    previewingStore()->draftedForPreview(1, 'default', 'en');

    // Held by the middleware, not by a method that only reads. Bagisto's layout
    // asks about the request itself, so a read that quietly answered for it
    // would decide what every other page draws.
    expect($request->attributes->has('appearance_previewing'))->toBeFalse();
});

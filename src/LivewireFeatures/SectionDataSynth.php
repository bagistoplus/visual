<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Sections\Support\SectionData;
use Illuminate\Support\Facades\Crypt;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Symfony\Component\Filesystem\Path;

class SectionDataSynth extends Synth
{
    public static $key = 'section';

    public static function match($target)
    {
        return $target instanceof SectionData;
    }

    public function dehydrate($target)
    {
        return [[
            'token' => Crypt::encrypt([
                'id' => $target->id,
                'source' => Path::makeRelative($target->sourceFile, base_path()),
            ]),
        ], []];
    }

    public function hydrate($value)
    {
        $data = Crypt::decrypt($value['token']);

        Visual::themeDataCollector()->collectSectionData($data['id'], base_path($data['source']));

        return Visual::themeDataCollector()->getSectionData($data['id']);
    }
}

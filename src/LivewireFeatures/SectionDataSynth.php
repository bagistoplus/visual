<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Sections\Support\SectionData;
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
            'id' => $target->id,
            'source' => Path::makeRelative($target->sourceFile, base_path()),
        ], []];
    }

    public function hydrate($value)
    {
        Visual::themeDataCollector()->collectSectionData($value['id'], base_path($value['source']));

        return Visual::themeDataCollector()->getSectionData($value['id']);
    }
}

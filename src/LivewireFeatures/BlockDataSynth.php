<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use BagistoPlus\Visual\Data\BlockData;
use Craftile\Laravel\Facades\BlockDatastore;
use Illuminate\Support\Facades\Crypt;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Symfony\Component\Filesystem\Path;

class BlockDataSynth extends Synth
{
    public static $key = 'block';

    public static function match($target)
    {
        return $target instanceof BlockData;
    }

    public function dehydrate($target)
    {
        return [[
            'token' => Crypt::encrypt([
                'id' => $target->id,
                'source' => Path::makeRelative($target->getSourceFile(), base_path()),
            ]),
        ], []];
    }

    public function hydrate($value)
    {
        $data = Crypt::decrypt($value['token']);
        BlockDatastore::loadFile(base_path($data['source']));

        $blockData = BlockDatastore::getBlock($data['id']);

        return $blockData;
    }
}

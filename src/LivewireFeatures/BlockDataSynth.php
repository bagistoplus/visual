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
                'source' => $this->portableSource($target->getSourceFile()),
            ]),
        ], []];
    }

    public function hydrate($value)
    {
        $data = Crypt::decrypt($value['token']);
        BlockDatastore::loadFile(str_contains($data['source'], '://') ? $data['source'] : base_path($data['source']));

        $blockData = BlockDatastore::getBlock($data['id']);

        return $blockData;
    }

    /**
     * A file is kept relative to the base path, which differs between servers.
     * A stream path, such as visual://, names the same data everywhere.
     */
    protected function portableSource(string $source): string
    {
        return str_contains($source, '://') ? $source : Path::makeRelative($source, base_path());
    }
}

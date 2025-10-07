<?php

namespace BagistoPlus\Visual\Data;

use BagistoPlus\Visual\Support\LiveUpdatesBuilder;
use Craftile\Laravel\BlockData as LaravelBlockData;

class BlockData extends LaravelBlockData
{
    public function __get($key)
    {
        if ($key === 'settings') {
            return $this->properties;
        } elseif ($key === 'editorAttributes' || $key === 'editor_attributes') {
            return $this->editorAttributes();
        }

        return null;
    }

    public function editorAttributes()
    {
        return $this->craftileAttributes();
    }

    public function liveUpdate(?string $propertyId = null, ?string $attr = null): LiveUpdatesBuilder
    {
        $builder = new LiveUpdatesBuilder(blockId: $this->id);

        if ($propertyId && $attr) {
            return $builder->attr($propertyId, $attr);
        }

        if ($propertyId) {
            return $builder->text($propertyId);
        }

        return $builder;
    }
}

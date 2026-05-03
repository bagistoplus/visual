<?php

namespace BagistoPlus\Visual\Data;

use AllowDynamicProperties;
use BagistoPlus\Visual\Support\LiveUpdatesBuilder;
use Craftile\Laravel\BlockData as LaravelBlockData;
use Craftile\Laravel\PropertyBag;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Block data with Visual-specific magic properties.
 *
 * @property-read PropertyBag $settings Access to block properties (alias for $properties)
 * @property-read Htmlable $editorAttributes Craftile editor attributes for rendering
 */
#[AllowDynamicProperties]
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

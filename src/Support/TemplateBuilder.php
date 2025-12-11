<?php

namespace BagistoPlus\Visual\Support;

use Craftile\Core\Data\Template as CraftileTemplate;

class TemplateBuilder extends CraftileTemplate
{
    /**
     * Add a section to the template (alias for block).
     *
     * @param  string  $id  Section ID (required)
     * @param  string|class-string<BlockPreset>  $type  Section type or preset class
     * @param  callable|null  $config  Optional callback to configure the section
     */
    public function section(string $id, string $type, ?callable $config = null): static
    {
        return $this->block($id, $type, $config);
    }
}

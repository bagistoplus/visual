<?php

namespace BagistoPlus\Visual\Data;

use Craftile\Core\Data\BlockSchema as CraftileBlockSchema;

/**
 * Extended BlockSchema with support for conditional visibility.
 */
class BlockSchema extends CraftileBlockSchema
{
    public array $enabledOn = [];

    public array $disabledOn = [];

    /**
     * Create schema from BlockInterface class.
     */
    public static function fromClass(string $blockClass): static
    {
        $schema = parent::fromClass($blockClass);

        // Check if block implements conditional visibility methods
        $schema->enabledOn = method_exists($blockClass, 'enabledOn') ? $blockClass::enabledOn() : [];
        $schema->disabledOn = method_exists($blockClass, 'disabledOn') ? $blockClass::disabledOn() : [];

        return $schema;
    }

    /**
     * Convert schema to array representation.
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'enabledOn' => $this->enabledOn,
            'disabledOn' => $this->disabledOn,
        ]);
    }
}

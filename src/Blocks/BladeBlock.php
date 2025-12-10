<?php

namespace BagistoPlus\Visual\Blocks;

use BagistoPlus\Visual\Concerns\HasBlockBehavior;
use BagistoPlus\Visual\Contracts\ConditionalBlockInterface;
use BagistoPlus\Visual\Data\BlockData;
use Craftile\Core\Contracts\BlockInterface;
use Illuminate\View\Component;

abstract class BladeBlock extends Component implements BlockInterface, ConditionalBlockInterface
{
    use HasBlockBehavior;

    public function __construct(public BlockData $block, protected array $context = [], public $children = null) {}

    public function data()
    {
        $this->attributes = $this->attributes ?: $this->newAttributeBag();

        return array_merge(
            $this->extractPublicProperties(),
            $this->extractPublicMethods(),
            $this->getVisualData(),
            $this->context
        );
    }

    /**
     * Get the methods that should be ignored.
     *
     * @return array
     */
    protected function ignoredMethods()
    {
        return array_merge(parent::ignoredMethods(), [
            'type',
            'slug',
            'name',
            'wrapper',
            'accepts',
            'description',
            'settings',
            'presets',
            'enabledOn',
            'disabledOn',
            'properties',
            'previewImageUrl',
            'icon',
            'category',
            'private',
            'getViewData',
            'share',
        ]);
    }

    protected function getVisualData()
    {
        $context = array_merge($this->context, $this->share());

        // $this->block->properties->setContext($context);

        return [
            'block' => $this->block,
            '__craftileContext' => $context,
        ];
    }

    /**
     * Get a value from the context by key.
     *
     * @param  string  $key  The context key to retrieve
     * @param  mixed  $default  Default value if key doesn't exist
     * @return mixed The context value or default
     */
    public function context(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }
}

<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Sections\Concerns\SectionTrait;
use BagistoPlus\Visual\Sections\Support\SectionData;
use Craftile\Core\Concerns\ContextAware;
use Craftile\Core\Concerns\IsBlock;
use Craftile\Laravel\BlockData;
use Illuminate\View\Component;

abstract class BladeSection extends Component implements SectionInterface
{
    use IsBlock, SectionTrait;

    public function __construct(public BlockData $block, protected array $context = [], public $children = null) {}

    public function __get($name)
    {
        if ($name === 'section') {
            return $this->block;
        }

        return null;
    }

    public function data()
    {
        $this->attributes = $this->attributes ?: $this->newAttributeBag();

        return array_merge(
            $this->extractPublicProperties(),
            $this->extractPublicMethods(),
            $this->getVisualSectionData(),
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
            'slug',
            'name',
            'getViewData',
        ]);
    }

    protected function getVisualSectionData()
    {
        return [
            'section' => $this->block,
        ];
    }
}

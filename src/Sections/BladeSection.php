<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Sections\Concerns\SectionTrait;
use Illuminate\View\Component;

abstract class BladeSection extends Component implements SectionInterface
{
    use SectionTrait;

    public function __construct(public string $visualId, protected array $viewData) {}

    public function data()
    {
        $this->attributes = $this->attributes ?: $this->newAttributeBag();

        return array_merge(
            $this->extractPublicProperties(),
            $this->extractPublicMethods(),
            $this->viewData
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
            'getSchemaPath',
            'getSchema',
        ]);
    }
}

<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Support\SimpleEmmetParser;
use JsonSerializable;

final class Section implements JsonSerializable
{
    /**
     * Blade/Livewire component is registered using this slug
     */
    public string $slug;

    /**
     * Shown in the editor left panel
     */
    public string $name;

    /**
     * An emmet formatted string of the section wrapper
     */
    public string $wrapper;

    /**
     * The section settings list
     */
    public array $settings;

    /**
     * List of blocks the section can accept with their configuration
     */
    public array $blocks;

    /**
     * The maximum number of blocks the section can accept
     */
    public int $maxBlocks;

    /**
     * Is this a livewire component ?
     */
    public bool $isLivewire;

    /**
     * The section description
     * Shown on the theme editor left panel when editing a section
     */
    public string $description;

    /**
     * The section preview image
     * Shown on the sections selector of the theme editor
     */
    public string $previewImageUrl;

    /**
     * The section preview description
     * Shown on the sections selector of the theme editor
     */
    public string $previewDescription;

    /**
     * The section default values
     * Useful for static sections
     */
    public array $default;

    public function __construct(
        $slug,
        $name,
        $wrapper = 'div',
        array $settings = [],
        array $blocks = [],
        $maxBlocks = 16,
        $description = '',
        $previewImageUrl = '',
        $previewDescription = '',
        $default = [],
        $isLivewire = false
    ) {
        $this->slug = $slug;
        $this->name = $name;
        $this->wrapper = $wrapper;
        $this->settings = $settings;
        $this->blocks = $blocks;
        $this->maxBlocks = $maxBlocks;
        $this->description = $description;
        $this->previewImageUrl = $previewImageUrl;
        $this->previewDescription = $previewDescription;
        $this->default = $default;
        $this->isLivewire = $isLivewire;
    }

    /**
     * Generate the section blade template
     */
    public function renderToBlade(string $id): string
    {
        $viewData = "collect(get_defined_vars()['__data'] ?: [])->except(['__env', 'app'])->all()";

        if ($this->isLivewire) {
            $component = sprintf("@livewire('visual-section-%s', ['visualId' => '%s', 'viewData' => %s])", $this->slug, $id, $viewData);
        } else {
            $component = sprintf('<x-visual-section-%s visualId="%s" :viewData="%s" />', $this->slug, $id, $viewData);
        }

        $template = $this->wrapper ? SimpleEmmetParser::parse($this->wrapper.'{__section__}') : '<div>{__section__}</div>';

        preg_match('/(<\w+)([>|\s].*)/', $template, $matches);
        $template = $matches[1].sprintf(' data-section-type="%s" data-section-id="%s"', $this->slug, $id).$matches[2];
        $template = str_replace('__section__', $component, $template);

        return $template;
    }

    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'wrapper' => $this->wrapper,
            'settings' => $this->settings,
            'blocks' => $this->blocks,
            'maxBlocks' => $this->maxBlocks,
            'description' => $this->description,
            'previewImageUrl' => $this->previewImageUrl,
            'previewDescription' => $this->previewDescription,
            'default' => $this->default,
            'isLivewire' => $this->isLivewire,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function createFromComponent(string $component)
    {
        $schema = $component::getSchema();

        return new self(
            slug: $component::slug(),
            name: $schema['name'],
            wrapper: $schema['wrapper'] ?? 'div',
            settings: $schema['settings'] ?? [],
            blocks: $schema['blocks'] ?? [],
            maxBlocks: $schema['maxBlocks'] ?? 16,
            description: $schema['description'] ?? ''
        );
    }
}

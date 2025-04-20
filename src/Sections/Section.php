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
        $schema = self::translateSchema($component::getSchema());

        return new self(
            slug: $component::slug(),
            name: $schema['name'] ?? $component::name(),
            wrapper: $schema['wrapper'] ?? 'div',
            settings: $schema['settings'] ?? [],
            blocks: $schema['blocks'] ?? [],
            maxBlocks: $schema['maxBlocks'] ?? 16,
            default: $schema['default'] ?? [],
            description: $schema['description'] ?? '',
            isLivewire: is_subclass_of($component, \Livewire\Component::class)
        );
    }

    protected static function translateSchema(array $schema): array
    {
        if (isset($schema['name'])) {
            $schema['name'] = __($schema['name']);
        }

        if (isset($schema['description'])) {
            $schema['description'] = __($schema['description']);
        }

        if (isset($schema['settings'])) {
            $schema['settings'] = self::translateSettings($schema['settings']);
        }

        if (isset($schema['blocks'])) {
            $schema['blocks'] = self::translateBlocks($schema['blocks']);
        }

        if (isset($schema['default'])) {
            $schema['default'] = self::translateSectionDefaults($schema['default']);
        }

        return $schema;
    }

    protected static function translateBlocks(array $blocks): array
    {
        return array_map(function ($block) {
            $block['name'] = __($block['name']);

            if (isset($block['settings'])) {
                $block['settings'] = self::translateSettings($block['settings']);
            }

            return $block;
        }, $blocks);
    }

    protected static function translateSettings(array $settings): array
    {
        return array_map(function ($setting) {
            $keys = ['label', 'default', 'info', 'placeholder'];

            foreach ($keys as $key) {
                if (isset($setting[$key])) {
                    $setting[$key] = __($setting[$key]);
                }
            }

            if (isset($setting['options']) && is_array($setting['options'])) {
                $setting['options'] = array_map(function ($option) {
                    $option['label'] = __($option['label']);

                    return $option;
                }, $setting['options']);
            }

            return $setting;
        }, $settings);
    }

    protected static function translateSectionDefaults(array $default)
    {
        if (isset($default['settings'])) {
            $default['settings'] = array_map(function ($value) {
                return __($value);
            }, $default['settings']);
        }

        if (isset($default['blocks'])) {
            $default['blocks'] = array_map(function ($block) {
                return self::translateSectionDefaults($block);
            }, $default['blocks']);
        }

        return $default;
    }
}

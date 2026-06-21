<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Blocks\BladeSection;
use BagistoPlus\Visual\Blocks\LivewireSection;
use BagistoPlus\Visual\Blocks\SimpleSection;
use BagistoPlus\Visual\Data\BlockSchema;
use Craftile\Laravel\BlockSchemaRegistry;
use Illuminate\Support\Collection;

class EditorBlockSchemaSerializer
{
    public function __construct(protected SchemaTextTranslator $schemaTextTranslator) {}

    public function all(): array
    {
        /** @var Collection<string, BlockSchema> $schemas */
        $schemas = collect(app(BlockSchemaRegistry::class)->all());

        return $schemas->map(function (BlockSchema $blockSchema) {
            $currentGroup = null;
            $properties = collect($blockSchema->properties)
                ->map(function ($prop) use (&$currentGroup) {
                    $propArray = $prop->toArray();

                    if ($propArray['type'] === 'header') {
                        $currentGroup = $this->schemaTextTranslator->translateText($propArray['label']);

                        return null;
                    }

                    if ($propArray['type'] === 'typography_presets') {
                        return null;
                    }

                    if ($currentGroup !== null) {
                        $propArray['group'] = $currentGroup;
                    }

                    return $this->schemaTextTranslator->translatePropertySchema($propArray);
                })
                ->filter()
                ->values()
                ->all();

            $meta = array_merge($blockSchema->meta, [
                'name' => $blockSchema->name,
                'icon' => $blockSchema->icon,
                'category' => $blockSchema->category,
                'description' => $blockSchema->description,
                'previewImageUrl' => $blockSchema->previewImageUrl,
                'isSection' => collect([SimpleSection::class, BladeSection::class, LivewireSection::class])
                    ->some(fn ($class) => is_subclass_of($blockSchema->class, $class)),
                'enabledOn' => $blockSchema->enabledOn,
                'disabledOn' => $blockSchema->disabledOn,
            ]);

            return [
                'type' => $blockSchema->type,
                'properties' => $properties,
                'accepts' => $blockSchema->accepts,
                'presets' => collect($blockSchema->presets)
                    ->map(fn ($preset) => $this->schemaTextTranslator->translatePreset(
                        is_object($preset) && method_exists($preset, 'toArray') ? $preset->toArray() : (array) $preset
                    ))
                    ->all(),
                'private' => $blockSchema->private,
                'meta' => $this->schemaTextTranslator->translateBlockMeta($meta),
            ];
        })
            ->values()
            ->all();
    }
}

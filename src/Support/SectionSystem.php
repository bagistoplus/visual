<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Data\BagistoSectionType;
use Illuminate\Support\Collection;

/**
 * Whether the installed Bagisto stores sections the way Bagisto mode reads and
 * writes them, and the section types a theme offers.
 *
 * Bagisto renamed ThemeCustomization to Section and moved drafts onto the row
 * in 2.5, then made section types extensible shortly after. Bagisto mode needs
 * both, so both are checked here and nothing else has to sniff versions.
 */
class SectionSystem
{
    protected const SECTION_MODEL = 'Webkul\Theme\Models\Section';

    protected const SECTION_SCHEMA = 'Webkul\Theme\SectionSchema';

    protected const VIEW_NAMESPACE = 'shop::sections.';

    /**
     * @var array<string, Collection<string, BagistoSectionType>>
     */
    protected array $cache = [];

    /**
     * Whether sections are stored, and their types declared, the way Bagisto
     * mode needs. A release that keeps its section types to itself is not
     * editable here, because no theme could add one.
     */
    public function available(): bool
    {
        return class_exists(self::SECTION_MODEL)
            && class_exists(self::SECTION_SCHEMA)
            && method_exists($this->schema(), 'types');
    }

    /**
     * The section types a theme offers, keyed by code, in the order the editor
     * lists them.
     *
     * Held for the rest of the request: the home page asks for the type of
     * every section it draws.
     *
     * @return Collection<string, BagistoSectionType>
     */
    public function types(?string $themeCode = null): Collection
    {
        if (! $this->available()) {
            return collect();
        }

        if (isset($this->cache[(string) $themeCode])) {
            return $this->cache[(string) $themeCode];
        }

        return $this->cache[(string) $themeCode] = collect($this->schema()->types($themeCode))
            ->map(fn (mixed $type) => new BagistoSectionType(
                code: $type->getCode(),
                title: $type->getTitle(),
                icon: $type->getIcon(),
                singleton: $type->isSingleton(),
                pinned: $type->isPinned(),
                rendersInLayout: $type->rendersInLayout(),
                view: $this->view($type),
                fieldsResolver: fn () => $type->getFields(),
            ))
            ->keyBy(fn (BagistoSectionType $type) => $type->code);
    }

    /**
     * The single type a code names, or null when the theme does not offer it.
     */
    public function type(?string $themeCode, ?string $code): ?BagistoSectionType
    {
        if (blank($code)) {
            return null;
        }

        return $this->types($themeCode)->get($code);
    }

    /**
     * Where the storefront draws a section of this type.
     *
     * A type names its own view once Bagisto lets it. Until then, and for any
     * type that stays quiet, the view is named after the type.
     */
    protected function view(mixed $type): string
    {
        if (method_exists($type, 'getView')) {
            return (string) $type->getView();
        }

        return self::VIEW_NAMESPACE.$type->getCode();
    }

    /**
     * Bagisto's section schema service, asked of the container so a store that
     * swapped it is the one answered about.
     */
    protected function schema(): mixed
    {
        return app(self::SECTION_SCHEMA);
    }
}

<?php

namespace BagistoPlus\Visual\Support;

/**
 * What the installed Bagisto's section system can do.
 *
 * Bagisto renamed ThemeCustomization to Section and moved drafts onto the row
 * in 2.5, then made section types extensible shortly after. Every check of
 * either lives here so nothing else has to sniff versions.
 */
class SectionSystem
{
    protected const SECTION_MODEL = 'Webkul\Theme\Models\Section';

    protected const SECTION_SCHEMA = 'Webkul\Theme\SectionSchema';

    /**
     * Whether sections are stored the way native mode reads and writes them,
     * which is Bagisto 2.5 onwards.
     */
    public function available(): bool
    {
        return class_exists(self::SECTION_MODEL)
            && class_exists(self::SECTION_SCHEMA);
    }

    /**
     * Whether a theme may declare its own section types, rather than being
     * offered the six Bagisto ships. Absent in 2.5.0-beta2.
     */
    public function supportsTypeRegistry(): bool
    {
        return $this->available()
            && method_exists(self::SECTION_SCHEMA, 'types');
    }
}

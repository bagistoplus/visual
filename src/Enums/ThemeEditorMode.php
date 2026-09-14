<?php

namespace BagistoPlus\Visual\Enums;

/**
 * How a theme is edited, which decides where its page structure is read from
 * and written to.
 */
enum ThemeEditorMode: string
{
    /**
     * Structure comes from the theme's visual template files.
     */
    case Visual = 'visual';

    /**
     * Structure comes from Bagisto's theme_sections rows.
     */
    case Bagisto = 'bagisto';

    /**
     * The theme cannot be edited, because it ships no visual templates and the
     * Bagisto it runs on has no section system.
     */
    case Unsupported = 'unsupported';

    public function isVisual(): bool
    {
        return $this === self::Visual;
    }

    public function isBagisto(): bool
    {
        return $this === self::Bagisto;
    }

    public function isEditable(): bool
    {
        return $this !== self::Unsupported;
    }
}

<?php

namespace BagistoPlus\Visual\Blocks;

/**
 * A section stored as a `theme_sections` row rather than declared as a class.
 *
 * Bagisto sections have no class of their own: their schema is read from the
 * section type its theme declares. This stands in as that schema's class, so
 * the editor can tell a section apart from the blocks nested inside it.
 */
class BagistoSection extends SimpleSection {}

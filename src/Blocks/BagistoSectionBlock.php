<?php

namespace BagistoPlus\Visual\Blocks;

/**
 * A block nested inside a Bagisto section: either a repeating list a section
 * type declares, or one entry of such a list.
 *
 * Like `BagistoSection`, this carries no schema of its own. It stands in as the
 * class of a schema read from a section type's fields.
 */
class BagistoSectionBlock extends SimpleBlock {}

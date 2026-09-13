<?php

namespace BagistoPlus\Visual\Settings;

/**
 * Source code edited in a highlighted editor, as Bagisto's static content
 * section stores its markup and styles.
 *
 * @method $this language(string $language)
 */
class Code extends Base
{
    protected static string $type = 'code';

    protected bool $localized = true;

    public function language(string $language): self
    {
        $this->meta['language'] = $language;

        return $this;
    }
}

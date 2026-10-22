<?php

namespace BagistoPlus\Visual\Support;

/**
 * Whether two sets of section options say the same thing.
 *
 * Options are rebuilt in the order the section type declares its fields, which
 * is rarely the order Bagisto happened to store them in, so comparing them as
 * they stand reports a change on every save. The keys of a map are unordered
 * and compared as such; the entries of a list are not, because the order the
 * merchant dragged them into is the thing being saved.
 */
class SectionOptions
{
    /**
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    public function same(array $a, array $b): bool
    {
        return $this->ordered($a) === $this->ordered($b);
    }

    protected function ordered(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $value = array_map(fn (mixed $item) => $this->ordered($item), $value);

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }
}

<?php

namespace BagistoPlus\Visual\Support;

/**
 * The order to hand Bagisto for a theme's sections, as positions across all of
 * them.
 *
 * The editor arranges the sections drawn on the page and the ones drawn on
 * every page as two lists, and Bagisto keeps one. Where the two interleave in
 * what is stored, and they do as soon as a section is appended after the
 * layout's, numbering one list after the other would move sections the
 * merchant never touched. So each stored position stays with the list whose
 * section held it, and takes that list's next section. A section never stored
 * goes last, where Bagisto's own editor appends one: the two lists are drawn
 * apart, so where one sits against the other is never seen.
 */
class SectionOrder
{
    /**
     * @param  array<int, int>  $stored  Row ids in the order the store holds them.
     * @param  array<int, array<int, int>>  $lists  Row ids of each list, in the order the editor left them.
     * @return array<int, int>
     */
    public static function merge(array $stored, array $lists): array
    {
        $listOf = [];

        foreach ($lists as $list => $rowIds) {
            foreach ($rowIds as $rowId) {
                $listOf[$rowId] = $list;
            }
        }

        $pending = $lists;
        $merged = [];

        foreach ($stored as $rowId) {
            if (! isset($listOf[$rowId])) {
                continue;
            }

            $merged[] = array_shift($pending[$listOf[$rowId]]);
        }

        return array_merge($merged, ...array_values($pending));
    }
}

/**
 * The ids Visual holds a Bagisto section's blocks under: `section-7` for the
 * section stored as row 7, and `section-7.images.1` for an entry of a list it
 * declares. Mirrors `BagistoPlus\Visual\Support\SectionBlockIds`.
 */
const SECTION = /^section-(\d+)$/;

/**
 * The row a block belongs to, whether the block is the section or something
 * nested inside it, or null when the id names no stored section: a visual
 * theme's block, or a section the editor has added but not saved yet.
 */
export function sectionRowId(blockId: string | null | undefined): number | null {
  const match = SECTION.exec(String(blockId ?? '').split('.')[0]);

  return match ? Number(match[1]) : null;
}

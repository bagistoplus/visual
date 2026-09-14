import { sectionBlockId } from './sectionBlockIds';

/**
 * Marks the sections Bagisto's layout draws so that the editor can select them.
 *
 * A section on the home page is drawn by a view Visual owns, which marks it with
 * the block id the editor holds it under. The footer and the services strip are
 * drawn by Bagisto's own layout, which Visual does not own and which marks them
 * its own way, with the row they are stored as. The two are the same section, so
 * the block id is written onto the element Bagisto already marked rather than
 * wrapping it in one of Visual's own.
 *
 * Craftile looks an element up as it needs it, so marking after the page has
 * been drawn is as good as marking while it is drawn.
 */
const SECTION_ATTRIBUTE = 'data-section-id';

const BLOCK_ATTRIBUTE = 'data-block';

/**
 * The blocks the page is holding, as the server described it. Anything else an
 * element claims to be is not a section this page is editing.
 */
export function knownBlockIds(doc: Document): Set<string> {
  const source = doc.getElementById('page-data')?.textContent;

  if (!source) {
    return new Set();
  }

  try {
    const data = JSON.parse(source);

    return new Set(Object.keys(data?.content?.blocks ?? data?.blocks ?? {}));
  } catch {
    return new Set();
  }
}

export function markLayoutSections(root: ParentNode, known: Set<string>): void {
  const marked = new Set<string>();

  root.querySelectorAll(`[${SECTION_ATTRIBUTE}]`).forEach((element) => {
    if (element.hasAttribute(BLOCK_ATTRIBUTE)) {
      return;
    }

    const rowId = Number(element.getAttribute(SECTION_ATTRIBUTE));

    if (!Number.isInteger(rowId) || rowId <= 0) {
      return;
    }

    const blockId = sectionBlockId(rowId);

    // The editor finds a block by the first element claiming to be it, so a
    // second element claiming the same would be pointed at arbitrarily.
    if (marked.has(blockId) || !known.has(blockId)) {
      return;
    }

    element.setAttribute(BLOCK_ATTRIBUTE, blockId);
    marked.add(blockId);
  });
}

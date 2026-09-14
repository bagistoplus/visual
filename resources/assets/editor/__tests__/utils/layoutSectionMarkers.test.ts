import { describe, expect, it } from 'vitest';
import { knownBlockIds, markLayoutSections } from '../../utils/layoutSectionMarkers';

function pageHolding(html: string, blocks: string[] | null): Document {
  const doc = document.implementation.createHTMLDocument();

  if (blocks) {
    const data = doc.createElement('script');
    data.id = 'page-data';
    data.textContent = JSON.stringify({
      content: { blocks: Object.fromEntries(blocks.map((id) => [id, { type: 'footer_links' }])) },
    });
    doc.body.appendChild(data);
  }

  doc.body.insertAdjacentHTML('beforeend', html);

  return doc;
}

function mark(doc: Document): Document {
  markLayoutSections(doc, knownBlockIds(doc));

  return doc;
}

describe('markLayoutSections', () => {
  it('holds a section bagisto marked under the id the editor knows it by', () => {
    const doc = mark(pageHolding('<footer data-section-id="9"></footer>', ['section-9']));

    expect(doc.querySelector('footer')?.getAttribute('data-block')).toBe('section-9');
  });

  it('leaves a section the page is not holding unmarked', () => {
    const doc = mark(pageHolding('<footer data-section-id="9"></footer>', ['section-7']));

    expect(doc.querySelector('footer')?.hasAttribute('data-block')).toBe(false);
  });

  it('leaves a section that was marked as it was drawn alone', () => {
    const doc = mark(
      pageHolding('<div data-section-id="9" data-block="section-7"></div>', ['section-9', 'section-7'])
    );

    expect(doc.querySelector('div[data-section-id]')?.getAttribute('data-block')).toBe('section-7');
  });

  it('marks the first of two elements claiming the same section, which is the one the editor would find', () => {
    const doc = mark(
      pageHolding('<div id="a" data-section-id="9"></div><div id="b" data-section-id="9"></div>', ['section-9'])
    );

    expect(doc.getElementById('a')?.getAttribute('data-block')).toBe('section-9');
    expect(doc.getElementById('b')?.hasAttribute('data-block')).toBe(false);
  });

  it('marks every section of a page that draws several', () => {
    const doc = mark(
      pageHolding('<div data-section-id="7"></div><footer data-section-id="9"></footer>', [
        'section-7',
        'section-9',
      ])
    );

    expect(doc.querySelector('div')?.getAttribute('data-block')).toBe('section-7');
    expect(doc.querySelector('footer')?.getAttribute('data-block')).toBe('section-9');
  });

  it('ignores an id that names no row', () => {
    const doc = mark(pageHolding('<footer data-section-id="nope"></footer>', ['section-9']));

    expect(doc.querySelector('footer')?.hasAttribute('data-block')).toBe(false);
  });

  it('marks nothing when the page said nothing about its blocks', () => {
    expect(knownBlockIds(pageHolding('', null)).size).toBe(0);

    const doc = mark(pageHolding('<footer data-section-id="9"></footer>', null));

    expect(doc.querySelector('footer')?.hasAttribute('data-block')).toBe(false);
  });

  it('marks nothing when what the page said cannot be read', () => {
    const doc = pageHolding('<footer data-section-id="9"></footer>', []);
    doc.getElementById('page-data')!.textContent = '{ not json';

    expect(mark(doc).querySelector('footer')?.hasAttribute('data-block')).toBe(false);
  });
});

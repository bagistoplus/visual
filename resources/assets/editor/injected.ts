import { PreviewClient } from '@craftile/preview-client';
import RawHtmlRenderer from '@craftile/preview-client-html';
import { Block } from '@craftile/types';
import morphdom from 'morphdom';

const previewClient = new PreviewClient();
let shouldIgnoreLivewireError = false;

const recentlyLiveUpdated = new Set<string>();
const liveUpdatedProperties = new Map<string, string>();

function markAsLiveUpdated(blockId: string, propertyKey: string): void {
  const attrName = `data-live-update-${blockId}.${propertyKey}`;
  liveUpdatedProperties.set(blockId, propertyKey);
  recentlyLiveUpdated.add(attrName);
}

function hasRecentLiveUpdate(el: HTMLElement): boolean {
  const attrs = Array.from(el.attributes);
  return attrs.some((attr) => recentlyLiveUpdated.has(attr.name));
}

window.addEventListener('error', (event) => {
  if (!shouldIgnoreLivewireError) {
    return;
  }

  if (event.message === 'Uncaught Could not find Livewire component in DOM tree') {
    event.preventDefault(); // Prevents it from showing in the console
  }
});

function createMorphdomHandler() {
  return function onBeforeElUpdated(fromEl: Element, toEl: Element): boolean {
    if (fromEl instanceof HTMLElement && hasRecentLiveUpdate(fromEl)) {
      return false;
    }

    if (fromEl instanceof HTMLElement && fromEl.hasAttribute('wire:id') && toEl.hasAttribute('wire:id')) {
      // @ts-ignore
      const livewireComponent = fromEl.__livewire;

      if (!livewireComponent) {
        return true;
      }

      const newSnapshot = toEl.getAttribute('wire:snapshot');
      const effects = JSON.parse(toEl.getAttribute('wire:effects') as string);

      effects.html = toEl.outerHTML;
      livewireComponent.mergeNewSnapshot(newSnapshot, effects);

      shouldIgnoreLivewireError = true;
      livewireComponent.processEffects(effects);

      setTimeout(() => {
        shouldIgnoreLivewireError = false;
      });

      return false;
    }

    // @ts-ignore
    if (fromEl['_x_dataStack'] && typeof window.Alpine?.morph === 'function') {
      window.Alpine.morph(fromEl, toEl, {
        updating(oldEl: Element, newEl: Element, childrenOnly: () => void) {
          if (oldEl instanceof HTMLElement && newEl instanceof HTMLElement) {
            if (hasRecentLiveUpdate(oldEl)) {
              return false;
            }

            if (oldEl.hasAttribute('wire:id')) {
              return childrenOnly();
            }
          }
        },
      });

      return false;
    }

    return true;
  };
}

RawHtmlRenderer.init(previewClient, {
  morphdom: {
    onBeforeElUpdated: createMorphdomHandler(),
  },
});

// Theme settings refresh - morph head and body separately
previewClient.on('page.refresh', (data: { html: string }) => {
  const parser = new DOMParser();
  const newDoc = parser.parseFromString(data.html, 'text/html');

  morphdom(document.head, newDoc.head, {
    childrenOnly: true,
  });

  morphdom(document.body, newDoc.body, {
    childrenOnly: true,
    onBeforeElUpdated: createMorphdomHandler(),
  });
});

function handlePropertyUpdate(data: { block: Block; key: string; value: any; oldValue: any }) {
  const { block, key, value } = data;

  // Clear old property when any property updates (even non-live-update ones)
  const lastUpdatedProperty = liveUpdatedProperties.get(block.id);
  if (lastUpdatedProperty && lastUpdatedProperty !== key) {
    const oldAttrName = `data-live-update-${block.id}.${lastUpdatedProperty}`;
    recentlyLiveUpdated.delete(oldAttrName);
    liveUpdatedProperties.delete(block.id);
  }

  const likeUpdateKey = [block.id, key].filter(Boolean).join('.');
  const attrName = `data-live-update-${likeUpdateKey}`;
  const selector = `[${CSS.escape(attrName)}]`;

  const elements = document.querySelectorAll(selector);

  if (!elements.length) {
    return;
  }

  markAsLiveUpdated(block.id, key);

  for (const el of elements) {
    const type = el.getAttribute(attrName);
    const [updateType, updateKey] = type?.split(/:(.+)/) ?? ['text', undefined];

    switch (updateType) {
      case 'text':
        el.textContent = value;
        break;
      case 'html':
        el.innerHTML = value;
        break;
      case 'outerHTML':
        el.outerHTML = value;
        break;
      case 'attr':
        if (!value) {
          if (el.tagName.toLowerCase() === 'img' && updateKey === 'src') {
            return false;
          }

          el.removeAttribute(updateKey as string);
        } else {
          el.setAttribute(updateKey as string, value);
        }
        break;
      case 'style':
        if (!value) {
          (el as HTMLElement).style.removeProperty(updateKey as string);
        } else {
          (el as HTMLElement).style.setProperty(updateKey as string, value);
        }
        break;
      case 'toggleClass':
        el.classList.toggle(updateKey as string);
        break;
      default:
        console.warn(`Unknown live update type: ${updateType}`);
    }
  }
}

previewClient.on('block.property.updated', handlePropertyUpdate);

class VisualObject {
  on(event: string, handler: (data: any) => void): () => void {
    const listener = ((e: CustomEvent) => {
      handler(e.detail);
    }) as EventListener;

    window.addEventListener(event, listener);

    return () => {
      window.removeEventListener(event, listener);
    };
  }

  off(event: string, handler: (data: any) => void): void {
    window.removeEventListener(event, handler as EventListener);
  }

  emit(event: string, data?: any): void {
    document.dispatchEvent(new CustomEvent(event, { detail: data }));
  }

  handleLiveUpdate(blockId: string, key: string, value: any): void {
    // Custom live update - delegates to existing handlePropertyUpdate
    handlePropertyUpdate({
      block: { id: blockId } as Block,
      key,
      value,
      oldValue: undefined,
    });
  }

  reload(): void {
    window.location.reload();
  }
}

const visual = new VisualObject();

previewClient.on('block.property.updated', (data) => {
  visual.emit('visual:block:setting:updated', data);
  if (data.block && !data.block.parentId) {
    visual.emit('visual:section:setting:updated', data);
  }
});

// Block adding
previewClient.on('block.insert.before', (data) => {
  visual.emit('visual:block:adding', data);

  if (data.block && !data.block.parentId) {
    visual.emit('visual:section:adding', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
  }
});

previewClient.on('block.insert.after', (data) => {
  visual.emit('visual:block:added', data);
  visual.emit('visual:block:load', data);

  if (data.block && !data.block.parentId) {
    visual.emit('visual:section:added', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
    visual.emit('visual:section:load', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
  }
});

// Block removing
previewClient.on('block.remove.before', (data) => {
  visual.emit('visual:block:removing', data);

  if (data.block && !data.block.parentId) {
    visual.emit('visual:section:removing', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
  }
});

previewClient.on('block.remove.after', (data) => {
  visual.emit('visual:block:removed', data);
  visual.emit('visual:block:unload', data);

  if (data.block && !data.block.parentId) {
    visual.emit('visual:section:removed', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
    visual.emit('visual:section:unload', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
  }
});

// Block moving
previewClient.on('block.move.before', (data) => {
  visual.emit('visual:block:moving', data);
  if (data.block && !data.block.parentId) {
    visual.emit('visual:section:moving', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
  }
});

previewClient.on('block.move.after', (data) => {
  visual.emit('visual:block:moved', data);
  if (data.block && !data.block.parentId) {
    visual.emit('visual:section:moved', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
  }
});

// Block updating
previewClient.on('block.update.before', (data) => {
  visual.emit('visual:block:updating', data);
  if (data.block && !data.block.parentId) {
    visual.emit('visual:section:updating', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
  }
});

previewClient.on('block.update.after', (data) => {
  visual.emit('visual:block:updated', data);
  visual.emit('visual:block:load', data);

  if (data.block && !data.block.parentId) {
    visual.emit('visual:section:updated', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
    visual.emit('visual:section:load', {
      ...data,
      sectionId: data.blockId,
      section: data.block,
    });
  }
});

declare global {
  interface Window {
    Visual: VisualObject;
  }
}

window.Visual = visual;

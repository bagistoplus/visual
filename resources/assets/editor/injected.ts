import { PreviewClient } from '@craftile/preview-client';
import RawHtmlRenderer from '@craftile/preview-client-html';
import { Block } from '@craftile/types';
import morphdom from 'morphdom';

const previewClient = new PreviewClient();

const livewireEnvelopeAttributes = ['wire:id', 'wire:snapshot', 'wire:effects', 'wire:key'];

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

function preserveLivewireEnvelope(fromEl: HTMLElement, toEl: HTMLElement): void {
  for (const attribute of livewireEnvelopeAttributes) {
    const value = fromEl.getAttribute(attribute);

    if (value === null) {
      toEl.removeAttribute(attribute);
    } else {
      toEl.setAttribute(attribute, value);
    }
  }
}

function morphKey(el: Element): string | undefined {
  return (el.getAttribute('data-block') ?? el.getAttribute('wire:key') ?? el.id) || undefined;
}

function morphWithAlpine(fromEl: HTMLElement, toEl: HTMLElement, livewireRoot?: HTMLElement): void {
  window.Alpine.morph(fromEl, toEl, {
    updating(
      oldEl: Element,
      newEl: Element,
      childrenOnly: () => void,
      skip: () => void
    ) {
      if (!(oldEl instanceof HTMLElement) || !(newEl instanceof HTMLElement)) {
        return;
      }

      if (oldEl.hasAttribute('data-morph-ignore') || hasRecentLiveUpdate(oldEl)) {
        skip();

        return;
      }

      if (oldEl !== livewireRoot && oldEl.hasAttribute('wire:id')) {
        childrenOnly();
      }
    },
    key: morphKey,
  });
}

function createMorphdomHandler() {
  return function onBeforeElUpdated(fromEl: Element, toEl: Element): boolean {
    if (fromEl instanceof HTMLElement && fromEl.hasAttribute('data-morph-ignore')) {
      return false;
    }

    if (fromEl instanceof HTMLElement && hasRecentLiveUpdate(fromEl)) {
      return false;
    }

    if (
      fromEl instanceof HTMLElement &&
      toEl instanceof HTMLElement &&
      fromEl.hasAttribute('wire:id') &&
      toEl.hasAttribute('wire:id')
    ) {
      if (fromEl.tagName !== toEl.tagName || typeof window.Alpine?.morph !== 'function') {
        fromEl.replaceWith(toEl);

        return false;
      }

      preserveLivewireEnvelope(fromEl, toEl);
      morphWithAlpine(fromEl, toEl, fromEl);

      return false;
    }

    // @ts-ignore
    if (
      fromEl instanceof HTMLElement &&
      toEl instanceof HTMLElement &&
      fromEl['_x_dataStack'] &&
      typeof window.Alpine?.morph === 'function'
    ) {
      morphWithAlpine(fromEl, toEl);

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
    const liveValue = normalizeLiveUpdateValue(value, updateType, updateKey);

    switch (updateType) {
      case 'text':
        el.textContent = liveValue;
        break;
      case 'html':
        el.innerHTML = liveValue;
        break;
      case 'outerHTML':
        el.outerHTML = liveValue;
        break;
      case 'attr':
        if (!liveValue) {
          if (el.tagName.toLowerCase() === 'img' && updateKey === 'src') {
            return false;
          }

          el.removeAttribute(updateKey as string);
        } else {
          el.setAttribute(updateKey as string, liveValue);
        }
        break;
      case 'style':
        if (!liveValue) {
          (el as HTMLElement).style.removeProperty(updateKey as string);
        } else {
          (el as HTMLElement).style.setProperty(updateKey as string, liveValue);
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

function normalizeLiveUpdateValue(value: any, updateType?: string, updateKey?: string): any {
  if (!isImageSettingValue(value)) {
    return value;
  }

  if (updateType === 'attr') {
    if (updateKey === 'src') {
      return value.url ?? value.path;
    }

    if (updateKey === 'alt') {
      return value.alt ?? '';
    }
  }

  if (updateType === 'style' && updateKey === 'object-position') {
    return focalPointObjectPosition(value.focalPoint);
  }

  return value.path;
}

function isImageSettingValue(
  value: any
): value is { path: string; url?: string; alt?: string; focalPoint?: { x?: number; y?: number } } {
  return typeof value === 'object' && value !== null && typeof value.path === 'string';
}

function focalPointObjectPosition(focalPoint?: { x?: number; y?: number }): string {
  const x = normalizePercentage(focalPoint?.x);
  const y = normalizePercentage(focalPoint?.y);

  return `${x}% ${y}%`;
}

function normalizePercentage(value: unknown): number {
  const numberValue = Number(value ?? 50);

  if (!Number.isFinite(numberValue)) {
    return 50;
  }

  return Math.min(100, Math.max(0, Math.round(numberValue)));
}

previewClient.on('block.property.updated', handlePropertyUpdate);

const EXTERNAL_LINK_MESSAGE =
  'This link cannot be opened inside the editor. It will be opened in a new window (:url). Click OK to continue.';

function findExternalLink(event: MouseEvent): string | null {
  if (event.defaultPrevented || event.button !== 0) {
    return null;
  }

  if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
    return null;
  }

  const target = event.target as Element | null;
  const anchor = target?.closest?.('a[href]') as HTMLAnchorElement | null;

  if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('download')) {
    return null;
  }

  let url: URL;

  try {
    url = new URL(anchor.getAttribute('href') || '', window.location.href);
  } catch {
    return null;
  }

  if (url.protocol !== 'http:' && url.protocol !== 'https:') {
    return null;
  }

  if (url.origin === window.location.origin) {
    return null;
  }

  return url.href;
}

function handleExternalLinkClick(event: MouseEvent): void {
  const href = findExternalLink(event);

  if (!href) {
    return;
  }

  event.preventDefault();

  if (window.confirm(EXTERNAL_LINK_MESSAGE.replace(':url', href))) {
    window.open(href, '_blank');
  }
}

document.addEventListener('click', handleExternalLinkClick, true);

class VisualObject {
  inDesignMode: true = true;

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

  isResponsiveValue(value: unknown): boolean {
    return typeof value === 'object' && value !== null && '_default' in value;
  }

  getResponsiveValue<T = any>(value: unknown, deviceId: string, fallback?: T): T {
    if (typeof value === 'object' && value !== null && '_default' in value) {
      return ((value as Record<string, any>)[deviceId] ?? (value as Record<string, any>)._default ?? fallback) as T;
    }
    return (value ?? fallback) as T;
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

type BlockEventPayload = {
  blockId?: string;
  block?: Block & { parentId?: string | null };
  key?: string;
  [key: string]: any;
};

function getBlockId(data: BlockEventPayload): string | undefined {
  return data.blockId ?? data.block?.id;
}

function isSectionBlock(data: BlockEventPayload): boolean {
  return Boolean(data.block && !data.block.parentId);
}

function toSectionPayload(data: BlockEventPayload): BlockEventPayload {
  return {
    ...data,
    sectionId: data.blockId,
    section: data.block,
  };
}

function emitEvent(event: string, data: any, scopeId?: string): void {
  visual.emit(event, data);

  if (scopeId) {
    visual.emit(`${event}:${scopeId}`, data);
  }
}

function emitBlockEvent(name: string, data: BlockEventPayload): void {
  emitEvent(`visual:block:${name}`, data, getBlockId(data));

  if (isSectionBlock(data)) {
    emitSectionEvent(name, data);
  }
}

function emitSectionEvent(name: string, data: BlockEventPayload): void {
  emitEvent(`visual:section:${name}`, toSectionPayload(data), getBlockId(data));
}

previewClient.on('block.property.updated', (data) => {
  emitEvent('visual:block:setting:updated', data, data.key);

  if (isSectionBlock(data)) {
    emitEvent('visual:section:setting:updated', data, data.key);
  }
});

// Block adding
previewClient.on('block.insert.before', (data) => {
  emitBlockEvent('adding', data);
});

previewClient.on('block.insert.after', (data) => {
  emitBlockEvent('added', data);
  emitBlockEvent('load', data);
});

// Block removing
previewClient.on('block.remove.before', (data) => {
  emitBlockEvent('removing', data);
});

previewClient.on('block.remove.after', (data) => {
  emitBlockEvent('removed', data);
  emitBlockEvent('unload', data);
});

// Block moving
previewClient.on('block.move.before', (data) => {
  emitBlockEvent('moving', data);
});

previewClient.on('block.move.after', (data) => {
  emitBlockEvent('moved', data);
});

// Block updating
previewClient.on('block.update.before', (data) => {
  emitBlockEvent('updating', data);
});

previewClient.on('block.update.after', (data) => {
  emitBlockEvent('updated', data);
  emitBlockEvent('load', data);
});

previewClient.on('block.select', (data) => {
  emitEvent('visual:block:selected', data, getBlockId(data));
});

previewClient.on('block.deselect', (data) => {
  emitEvent('visual:block:deselected', data, getBlockId(data));
});

declare global {
  interface Window {
    Visual: VisualObject;
  }
}

window.Visual = visual;

import morphdom from 'morphdom';
import { BlockData, SectionData } from './types';

declare global {
  interface Window {
    Visual: VisualObject;
  }
}

interface LiveUpdateOptions {
  target: string;
  text?: boolean;
  html?: boolean;
  attr?: string;
  style?: string;
  handler?(el: HTMLElement, value: any): void;
  transform?(value: any): any;
}

enum ATTRS {
  SectionId = 'data-section-id',
  SectionType = 'data-section-type',
  VisualInit = 'data-visualized',
  SectionName = 'data-section-name',
  VisualHighlighted = 'data-visual-highlighted',
}

/**
 * Action types for post messages to parent window
 */
const ACTIONS = {
  INITIALIZE: 'initialize',
  MOVE_SECTION_UP: 'section:move-up',
  MOVE_SECTION_DOWN: 'section:move-down',
  EDIT_SECTION: 'section:edit',
  TOGGLE_SECTION: 'section:toggle',
  REMOVE_SECTION: 'section:remove',
  SET_USED_COLORS: 'usedColors',
} as const;

const EVENTS = {
  prefix: 'visual:',
  EDITOR_INITIALIZED: 'editor:init',
  SETTING_UPDATED: 'setting:updated',
  SECTION_HIGHLIGHT: 'section:highlight',
  SECTION_UNHIGHLIGHT: 'section:unhighlight',
  SECTION_SELECT: 'section:select',
  SECTION_DESELECT: 'section:deselect',
  SECTION_LOAD: 'section:load',
  SECTION_UNLOAD: 'section:unload',

  BLOCK_SELECT: 'block:select',
  BLOCK_DESELECT: 'block:deselect',
  SECTION_ADDED: 'section:added',
  SECTION_REMOVED: 'section:removed',
  SECTIONS_REORDERED: 'sectionsOrder',
  REFRESH_PREVIEW: 'refresh',
  REORDERING: 'reordering',
} as const;

type Unsubscribe = () => void;

class VisualObject {
  inDesignMode = true;
  inPreviewMode = false;

  on<T>(event: string, handler: (detail: T) => void): Unsubscribe {
    const name = event.startsWith(EVENTS.prefix) ? event : EVENTS.prefix + event;
    const wrapped = (e: Event) => handler((e as CustomEvent<T>).detail);

    document.addEventListener(name, wrapped);

    return () => document.removeEventListener(name, wrapped);
  }

  _dispatch<T>(event: string, detail: T) {
    const name = EVENTS.prefix + event;
    document.dispatchEvent(new CustomEvent(name, { detail, bubbles: true }));
  }

  /**
   * Set up live updates for a section type
   * @param sectionType Section type to handle
   * @param mappings Update mappings configuration
   */
  handleLiveUpdate(
    sectionType: string,
    mappings: {
      section?: Record<string, LiveUpdateOptions>;
      blocks?: Record<string, Record<string, LiveUpdateOptions>>;
    }
  ) {
    this.on<{
      data: { section: SectionData; block?: BlockData; settingId: string; settingValue: any };
      skipRefresh: () => void;
    }>(EVENTS.SETTING_UPDATED, ({ data, skipRefresh }) => {
      const { section, block, settingId, settingValue } = data;

      if (!section || section.type !== sectionType) {
        return;
      }

      const container = document.querySelector(`[${ATTRS.SectionId}="${section.id}"]`) as HTMLElement;
      if (!container) {
        return;
      }

      let config: LiveUpdateOptions | ((value: any) => void) | undefined;

      if (block && mappings.blocks?.[block.type]) {
        config = mappings.blocks[block.type][settingId];
      } else if (mappings.section) {
        config = mappings.section[settingId];
      }

      if (!config) {
        return;
      }

      if (typeof config === 'function') {
        // then use it as a custom handler, no target
        config(settingValue);
        return skipRefresh();
      }

      const targetEl = config.target ? (container.querySelector(config.target) as HTMLElement) : null;

      if (!targetEl) {
        return;
      }

      const value = typeof config.transform === 'function' ? config.transform(settingValue) : settingValue;

      if (typeof config.handler === 'function') {
        config.handler(targetEl, value);
      } else if (config.html) {
        targetEl.innerHTML = value;
      } else if (config.text) {
        targetEl.textContent = value;
      } else if (config.style) {
        (targetEl.style as any)[config.style] = value;
      } else if (config.attr) {
        targetEl.setAttribute(config.attr, value);
      } else {
        return;
      }

      skipRefresh();
    });
  }
}

window.Visual = new VisualObject();

class ThemeEditor {
  private sectionOverlay!: HTMLDivElement;
  private sectionLabel!: HTMLElement;
  private moveUpBtn!: HTMLButtonElement;
  private moveDownBtn!: HTMLButtonElement;
  private editBtn!: HTMLButtonElement;
  private disableBtn!: HTMLButtonElement;
  private removeBtn!: HTMLButtonElement;
  private buttonsContainer!: HTMLDivElement;

  private activeSectionId: string | null = null;
  private sectionsOrder: string[] = [];
  private hoverDebounce = 0;
  private reorderingSectionId: string | null = null;

  private sectionContainers = new Map<string, HTMLElement>();

  private discardLivewireComponentNotFoundError = false;

  private messageHandlers: Record<string, (data: any, messageId?: string) => void> = {
    [EVENTS.SECTION_HIGHLIGHT]: (data) => this.handleSectionHighlight(data),
    [EVENTS.SECTION_SELECT]: (data) => this.handleSectionSelected(data),
    [EVENTS.SECTION_DESELECT]: (data) => this.handleSectionDeselected(data),
    [EVENTS.BLOCK_SELECT]: (data) => this.handleBlockSelected(data),
    [EVENTS.BLOCK_DESELECT]: (data) => this.handleBlockDeselected(data),
    [EVENTS.SECTION_ADDED]: (data) => this.handleSectionAdded(data),
    [EVENTS.SECTION_REMOVED]: (data) => this.handleSectionRemoved(data),
    [EVENTS.SECTION_UNHIGHLIGHT]: () => this.handleUnhighlightSection(),
    [EVENTS.SECTIONS_REORDERED]: (data) => this.handleSectionsReordered(data),
    [EVENTS.REORDERING]: (data) => this.handleReordering(data),
    [EVENTS.REFRESH_PREVIEW]: (data) => this.refreshPreviewer(data),
    [EVENTS.SETTING_UPDATED]: (data, messageId) => this.handleSettingUpdated(data, messageId),
  };

  init() {
    this.initializeUIElements();
    this.attachButtonEvents();
    this.attachMouseEvents();

    this.postMessage(ACTIONS.INITIALIZE, {
      themeData: window.themeData,
      templates: window.templates,
      settingsSchema: window.settingsSchema,
      preloadedModels: window.preloadedModels,
    });

    this.sectionsOrder = window.themeData.sectionsOrder;
    this.buildSectionContainers();

    window.addEventListener('message', ({ data }) => this.handleMessage(data));
    window.addEventListener('resize', () => this.handleWindowResize());

    this.extractUsedColors();

    window.addEventListener('error', (event) => {
      if (!this.discardLivewireComponentNotFoundError) {
        return;
      }

      if (event.message === 'Uncaught Could not find Livewire component in DOM tree') {
        event.preventDefault(); // Prevents it from showing in the console
      }
    });
  }

  private initializeUIElements() {
    this.sectionOverlay = document.querySelector('#section-overlay') as HTMLDivElement;
    this.sectionLabel = document.querySelector('#label') as HTMLElement;
    this.buttonsContainer = document.querySelector('#buttons') as HTMLDivElement;
    this.moveUpBtn = this.sectionOverlay.querySelector('#move-up') as HTMLButtonElement;
    this.moveDownBtn = this.sectionOverlay.querySelector('#move-down') as HTMLButtonElement;
    this.editBtn = this.sectionOverlay.querySelector('#edit') as HTMLButtonElement;
    this.disableBtn = this.sectionOverlay.querySelector('#disable') as HTMLButtonElement;
    this.removeBtn = this.sectionOverlay.querySelector('#remove') as HTMLButtonElement;
  }

  private buildSectionContainers() {
    document.querySelectorAll(`[${ATTRS.SectionId}]`).forEach((el) => {
      this.sectionContainers.set((el as HTMLElement).dataset.sectionId!, el.parentNode as HTMLElement);
    });
  }

  private findCommentParent(text: string): Node | null {
    const iterator = document.createNodeIterator(document.body, NodeFilter.SHOW_COMMENT, {
      acceptNode(node) {
        return node.nodeValue?.trim() === text ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_SKIP;
      },
    });

    const commentNode = iterator.nextNode();
    return commentNode?.parentNode ?? null;
  }

  private attachButtonEvents() {
    this.moveUpBtn.onclick = () => this.postMessage(ACTIONS.MOVE_SECTION_UP, this.activeSectionId);
    this.moveDownBtn.onclick = () => this.postMessage(ACTIONS.MOVE_SECTION_DOWN, this.activeSectionId);
    this.editBtn.onclick = () => this.postMessage(ACTIONS.EDIT_SECTION, this.activeSectionId);
    this.disableBtn.onclick = () => this.postMessage(ACTIONS.TOGGLE_SECTION, this.activeSectionId);
    this.removeBtn.onclick = () => this.postMessage(ACTIONS.REMOVE_SECTION, this.activeSectionId);
  }

  private attachMouseEvents() {
    document.addEventListener(
      'mouseover',
      (e) => {
        if (!(e.target instanceof Element)) {
          return;
        }

        const section = (e.target as Element).closest(`[${ATTRS.SectionType}]`) as HTMLElement;

        if (section) {
          this.handleUnhighlightSection();
          this.buttonsContainer.style.display = 'flex';
          this.debounceFocusSection(section);
        }
      },
      { passive: true }
    );

    document.addEventListener(
      'mouseleave',
      (e) => {
        if (!(e.target instanceof Element)) {
          this.buttonsContainer.style.display = 'none';
          this.clearActiveSection();
          return;
        }

        const section = (e.target as Element).closest(`[${ATTRS.SectionType}]`) as HTMLElement;
        if (!section) {
          return;
        }

        const toEl = e.relatedTarget;
        if (!toEl || !this.sectionOverlay.contains(toEl as Node)) {
          this.buttonsContainer.style.display = 'none';
          this.clearActiveSection();
        }
      },
      { passive: true }
    );
  }

  private handleWindowResize() {
    if (this.activeSectionId) {
      const activeSection = document.querySelector(`[${ATTRS.SectionId}="${this.activeSectionId}"]`) as HTMLElement;

      if (activeSection) {
        this.focusOnSection(activeSection);
      }
    }
  }

  private debounceFocusSection(section: HTMLElement) {
    clearTimeout(this.hoverDebounce);
    this.hoverDebounce = window.setTimeout(() => {
      this.focusOnSection(section);
    }, 50);
  }

  private handleMessage({ type, data, messageId }: { type: string; data: any; messageId?: string }) {
    const handler = this.messageHandlers[type];
    if (handler) {
      handler(data, messageId);
    } else {
      window.Visual._dispatch(type, data);
    }
  }

  private handleSectionHighlight(id: string) {
    if (this.reorderingSectionId) {
      return;
    }

    const el = document.querySelector(`[${ATTRS.SectionId}="${id}"]`) as HTMLElement;

    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      el.setAttribute(ATTRS.VisualHighlighted, 'true');
    }
  }

  private handleSectionSelected(id: string) {
    if (this.activeSectionId === id) {
      return;
    }

    this.activeSectionId = id;
    const el = document.querySelector(`[${ATTRS.SectionId}="${id}"]`) as HTMLElement;

    if (!el) {
      return;
    }

    this.handleSectionHighlight(id);
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });

    window.Visual._dispatch(EVENTS.SECTION_SELECT, {
      section: {
        id,
        type: el.dataset.sectionType,
      },
    });

    window.Visual._dispatch(EVENTS.SECTION_SELECT + `:${id}`, {});
  }

  private handleSectionDeselected(id: string) {
    if (this.activeSectionId === id) {
      this.clearActiveSection();
      this.activeSectionId = null;
    }

    const el = document.querySelector(`[${ATTRS.SectionId}="${id}"]`) as HTMLElement;
    if (!el) {
      return;
    }

    el.removeAttribute(ATTRS.VisualHighlighted);

    window.Visual._dispatch(EVENTS.SECTION_DESELECT, { section: { id, type: el.dataset.sectionType } });
    window.Visual._dispatch(EVENTS.SECTION_DESELECT + `:${id}`, {});
  }

  private handleBlockSelected(data: { sectionId: string; blockId: string }) {
    this.handleSectionSelected(data.sectionId);
    window.Visual._dispatch(EVENTS.BLOCK_SELECT, data);
    window.Visual._dispatch(EVENTS.BLOCK_SELECT + `:${data.blockId}`, {});
  }

  private handleBlockDeselected(data: { sectionId: string; blockId: string }) {
    window.Visual._dispatch(EVENTS.BLOCK_DESELECT, data);
    window.Visual._dispatch(EVENTS.BLOCK_DESELECT + `:${data.blockId}`, {});
  }

  private handleSectionAdded({ section }: { section: SectionData }) {
    this.handleSectionHighlight(section.id);
    window.Visual._dispatch(EVENTS.SECTION_ADDED, { section });
  }

  private handleSectionRemoved(data: { id: string }) {
    const el = document.querySelector(`[${ATTRS.SectionId}="${data.id}"]`);

    if (el) {
      el.remove();
    }
  }

  private handleUnhighlightSection() {
    document.querySelectorAll(`[${ATTRS.VisualHighlighted}]`).forEach((el) => {
      el.removeAttribute(ATTRS.VisualHighlighted);
    });
  }

  private handleSectionsReordered(order: string[]) {
    this.sectionsOrder = order;
    this.reorderingSectionId = null;

    document.querySelectorAll('[data-reordering]').forEach((el) => {
      el.removeAttribute('data-reordering');
    });
  }

  private handleReordering(data: { order: string[]; sectionId: string }) {
    this.reorderingSectionId = data.sectionId;
    const sectionsContainer = this.findCommentParent('BEGIN: template') as HTMLElement;

    data.order.forEach((id) => {
      const el = sectionsContainer.querySelector(`[${ATTRS.SectionId}="${id}"]`) as HTMLElement;
      if (el?.parentElement) {
        el.parentElement.appendChild(el);
      }
    });

    const movedEl = document.querySelector(`[${ATTRS.SectionId}="${data.sectionId}"]`) as HTMLElement;

    if (movedEl) {
      requestAnimationFrame(() => {
        movedEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        movedEl.dataset.reordering = 'true';
      });
    }
  }

  private handleSettingUpdated(
    data: {
      section: SectionData;
      block?: BlockData;
      settingId: string;
      settingValue: any;
    },
    messageId?: string
  ) {
    let skipRefresh = this.autoHandleLiveUpdate(data);

    if (!skipRefresh) {
      window.Visual._dispatch(EVENTS.SETTING_UPDATED, {
        data,
        skipRefresh: () => {
          skipRefresh = true;
        },
      });
    }

    if (this.activeSectionId) {
      const el = document.querySelector(`[${ATTRS.SectionId}="${this.activeSectionId}"]`) as HTMLElement;
      if (el) {
        this.focusOnSection(el);
        // el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }

    if (messageId) {
      window.parent.postMessage({ messageId, skipRefresh }, window.origin);
    }
  }

  private focusOnSection(section: HTMLElement) {
    // this.activeSectionId = section.dataset.sectionId!;
    const rect = section.getBoundingClientRect();
    window.requestAnimationFrame(() => {
      Object.assign(this.sectionOverlay.style, {
        display: 'block',
        width: `${rect.width}px`,
        height: `${rect.height}px`,
        left: `${rect.left + window.scrollX}px`,
        top: `${rect.top + window.scrollY}px`,
      });
      this.sectionLabel.textContent = section.dataset.sectionName || '';

      const position = this.sectionsOrder.indexOf(section.dataset.sectionId!);
      this.moveUpBtn.style.display = position > 0 ? 'inline' : 'none';
      this.moveDownBtn.style.display = position > 0 && position < this.sectionsOrder.length - 1 ? 'inline' : 'none';
    });
  }

  private clearActiveSection() {
    // this.activeSectionId = null;
    this.sectionOverlay.style.display = 'none';
  }

  private autoHandleLiveUpdate(data: {
    section: SectionData;
    block?: BlockData;
    settingId: string;
    settingValue: any;
  }): boolean {
    const { section, block, settingId, settingValue } = data;
    const key = [section?.id, block?.id, settingId].filter(Boolean).join('.');
    const attrName = `data-live-update-${key}`;
    const selector = `[${CSS.escape(attrName)}]`;

    const elements = document.querySelectorAll(selector);
    if (!elements.length) {
      return false;
    }

    for (const el of elements) {
      const type = el.getAttribute(attrName);
      const [updateType, updateKey] = type?.split(/:(.+)/) ?? ['text', undefined];

      switch (updateType) {
        case 'text':
          el.textContent = settingValue;
          break;
        case 'html':
          el.innerHTML = settingValue;
          break;
        case 'outerHTML':
          el.outerHTML = settingValue;
          break;
        case 'attr':
          if (!settingValue) {
            if (el.tagName.toLowerCase() === 'img' && updateKey === 'src') {
              return false;
            }

            el.removeAttribute(updateKey as string);
          } else {
            el.setAttribute(updateKey as string, settingValue);
          }
          break;
        case 'style':
          if (!settingValue) {
            (el as HTMLElement).style.removeProperty(updateKey as string);
          } else {
            (el as HTMLElement).style.setProperty(updateKey as string, settingValue);
          }
          break;
        case 'toggleClass':
          el.classList.toggle(updateKey as string);
          break;
        default:
          console.warn(`Unknown live update type: ${updateType}`);
      }
    }

    return true;
  }

  private patchNode(nodeFrom: Element, nodeTo: Element) {
    const self = this;
    morphdom(nodeFrom, nodeTo, {
      onBeforeElUpdated(fromEl, toEl) {
        if (fromEl instanceof HTMLElement && fromEl.hasAttribute('wire:id')) {
          // @ts-ignore
          const livewireComponent = fromEl.__livewire;
          const newSnapshot = toEl.getAttribute('wire:snapshot');
          const effects = JSON.parse(toEl.getAttribute('wire:effects') as string);

          effects.html = toEl.outerHTML;
          livewireComponent.mergeNewSnapshot(newSnapshot, effects);

          self.discardLivewireComponentNotFoundError = true;
          livewireComponent.processEffects(effects);

          setTimeout(() => {
            self.discardLivewireComponentNotFoundError = false;
          });

          return false;
        }

        // @ts-ignore
        if (fromEl['_x_dataStack'] && typeof window.Alpine?.morph === 'function') {
          window.Alpine.morph(fromEl, toEl, {
            updating(oldEl: Element, newEl: Element, childrenOnly: () => void) {
              if (oldEl instanceof HTMLElement && newEl instanceof HTMLElement) {
                if (oldEl.hasAttribute('wire:id')) {
                  return childrenOnly();
                }
              }
            },
          });

          return false;
        }

        return true;
      },
    });
  }

  private patchScripts(existingContainer: Element, newContainer: Element) {
    const existingScripts = Array.from(existingContainer.querySelectorAll('script'));
    const newScripts = Array.from(newContainer.querySelectorAll('script'));

    newScripts.forEach((newScript) => {
      const isInline = !newScript.src;
      const matchesExisting = existingScripts.some((existingScript) => {
        // Match external scripts by src
        if (!isInline && existingScript.src === newScript.src) return true;

        // Match inline scripts by content and attributes
        if (isInline && existingScript.textContent === newScript.textContent) {
          return Array.from(newScript.attributes).every(
            (attr) => existingScript.getAttribute(attr.name) === attr.value
          );
        }

        return false;
      });

      if (!matchesExisting) {
        const executableScript = document.createElement('script');

        // Copy attributes
        Array.from(newScript.attributes).forEach((attr) => {
          executableScript.setAttribute(attr.name, attr.value);
        });

        // Copy inline content
        if (isInline) {
          executableScript.textContent = newScript.textContent;
        }

        // Append to existing container to execute
        existingContainer.appendChild(executableScript);
      }
    });
  }

  private refreshPreviewer({ html, updatedSections }: { html: string; updatedSections: Map<string, any> }) {
    const newDoc = new DOMParser().parseFromString(html, 'text/html');
    const sectionContainers = this.sectionContainers;

    morphdom(document.head, newDoc.head);

    if (updatedSections.size === 0) {
      this.patchNode(document.body, newDoc.body);
      window.Visual._dispatch('page:load', {});
      // document.documentElement.innerHTML = newDoc.documentElement.innerHTML;
    } else {
      const templateContainer = this.findCommentParent('BEGIN: template') as HTMLElement;
      const sections = document.querySelectorAll(`[${ATTRS.SectionType}]`);

      updatedSections.forEach((context: any, sectionId: string) => {
        const oldEl = document.querySelector(`[data-section-id="${sectionId}"]`);
        const newEl = newDoc.querySelector(`[data-section-id="${sectionId}"]`);

        if (oldEl && newEl) {
          window.Visual._dispatch(EVENTS.SECTION_UNLOAD, context);
          this.patchNode(oldEl, newEl);
        } else if (!oldEl && newEl) {
          const position = context.position ?? sections.length;
          const sectionId = (newEl as HTMLElement).dataset.sectionId as string;

          if (position <= 0) {
            let parent = sectionContainers.get(sectionId) ?? templateContainer;
            parent.insertBefore(newEl, parent.firstChild);

            if (!sectionContainers.has(sectionId)) {
              sectionContainers.set(sectionId, parent);
            }
          } else if (position >= sections.length) {
            let parent = sectionContainers.get(sectionId) ?? templateContainer;
            parent.appendChild(newEl);

            if (!sectionContainers.has(sectionId)) {
              sectionContainers.set(sectionId, parent);
            }
          } else {
            const nextSection = sections[position];
            nextSection.parentNode?.insertBefore(newEl, nextSection);
          }
        } else {
          return;
        }

        window.Visual._dispatch(EVENTS.SECTION_LOAD, context);
      });
    }

    this.patchScripts(document.body, newDoc.body);

    if (this.activeSectionId) {
      const el = document.querySelector(`[${ATTRS.SectionId}="${this.activeSectionId}"]`) as HTMLElement;
      if (el) {
        this.focusOnSection(el);
      }
    }
  }

  private postMessage(type: string, data: any) {
    window.parent.postMessage({ type, data }, window.origin);
  }

  private extractUsedColors(limit = 6) {
    const counts = new Map<string, number>();
    document.querySelectorAll('*').forEach((el) => {
      const style = getComputedStyle(el);
      [style.backgroundColor, style.color].forEach((color) => {
        if (color && color !== 'transparent' && color !== 'rgba(0, 0, 0, 0)') {
          counts.set(color, (counts.get(color) || 0) + 1);
        }
      });
    });
    const topColors = Array.from(counts.entries())
      .sort((a, b) => b[1] - a[1])
      .slice(0, limit)
      .map(([color]) => color);
    this.postMessage(ACTIONS.SET_USED_COLORS, topColors);
  }
}

window.addEventListener('DOMContentLoaded', () => {
  const editor = new ThemeEditor();
  editor.init();
  document.dispatchEvent(
    new CustomEvent(EVENTS.prefix + EVENTS.EDITOR_INITIALIZED, {
      bubbles: true,
    })
  );
});

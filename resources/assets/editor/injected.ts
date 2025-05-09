import { Idiomorph } from 'idiomorph/dist/idiomorph.esm.js';
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
  SECTION_SELECTED: 'section:selected',
  SECTION_REMOVED: 'section:removed',
  CLEAR_ACTIVE_SECTION: 'clearActiveSection',
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
    document.dispatchEvent(new CustomEvent(name, { detail }));
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

      let config: LiveUpdateOptions | undefined;

      if (block && mappings.blocks?.[block.type]) {
        config = mappings.blocks[block.type][settingId];
      } else if (mappings.section) {
        config = mappings.section[settingId];
      }

      if (!config) {
        return;
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

  private messageHandlers: Record<string, (data: any, messageId?: string) => void> = {
    [EVENTS.SECTION_HIGHLIGHT]: (data) => this.handleSectionHighlight(data),
    [EVENTS.SECTION_SELECTED]: (data) => this.handleSectionSelected(data),
    [EVENTS.SECTION_REMOVED]: (data) => this.handleSectionRemoved(data),
    [EVENTS.CLEAR_ACTIVE_SECTION]: () => this.handleClearActiveSection(),
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
    });

    this.sectionsOrder = window.themeData.sectionsOrder;

    window.addEventListener('message', ({ data }) => this.handleMessage(data));
    window.addEventListener('resize', () => this.handleWindowResize());

    this.extractUsedColors();
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

        if (section && section.dataset.sectionId !== this.activeSectionId) {
          this.buttonsContainer.style.display = 'flex';
          this.debounceHighlight(section);
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
        this.highlightSection(activeSection);
      }
    }
  }

  private debounceHighlight(section: HTMLElement) {
    clearTimeout(this.hoverDebounce);
    this.hoverDebounce = window.setTimeout(() => {
      this.highlightSection(section);
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

    const el = document.querySelector(`[${ATTRS.SectionId}="${id}"]`) as HTMLElement;

    if (!el) {
      return;
    }

    this.highlightSection(el);
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    window.Visual._dispatch(EVENTS.SECTION_SELECTED, {
      section: {
        id: el.dataset.sectionId,
        type: el.dataset.sectionType,
      },
    });
  }

  private handleSectionRemoved(data: { id: string }) {
    const el = document.querySelector(`[${ATTRS.SectionId}="${data.id}"]`);

    if (el) {
      el.remove();
    }
  }

  private handleClearActiveSection() {
    this.clearActiveSection();
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

    data.order.forEach((id) => {
      const el = document.querySelector(`[${ATTRS.SectionId}="${id}"]`) as HTMLElement;
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
        this.highlightSection(el);
      }
    }

    if (messageId) {
      window.parent.postMessage({ messageId, skipRefresh }, window.origin);
    }
  }

  private highlightSection(section: HTMLElement) {
    this.activeSectionId = section.dataset.sectionId!;
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

      const position = this.sectionsOrder.indexOf(this.activeSectionId!);
      this.moveUpBtn.style.display = position > 0 ? 'inline' : 'none';
      this.moveDownBtn.style.display = position > 0 && position < this.sectionsOrder.length - 1 ? 'inline' : 'none';
    });
  }

  private clearActiveSection() {
    this.activeSectionId = null;
    this.sectionOverlay.style.display = 'none';
  }

  private autoHandleLiveUpdate(data: {
    section: SectionData;
    block?: BlockData;
    settingId: string;
    settingValue: any;
  }): boolean {
    const { section, block, settingId, settingValue } = data;
    const key = [section?.id, block?.id, settingId].filter(Boolean).join(':');
    const el = document.querySelector(`[data-live-update-key="${key}"]`) as HTMLElement;
    if (!el) {
      return false;
    }
    if (el.dataset.liveUpdateAttr) {
      el.setAttribute(el.dataset.liveUpdateAttr, settingValue);
    } else {
      el.innerHTML = settingValue;
    }
    return true;
  }

  private refreshPreviewer(html: string) {
    const newDoc = new DOMParser().parseFromString(html, 'text/html');
    Idiomorph.morph(document.documentElement, newDoc.documentElement, {
      callbacks: {
        beforeNodeMorphed(fromEl: Element, toEl: Element) {
          // @ts-ignore
          if (fromEl['_x_dataStack'] && typeof window.Alpine?.morph === 'function') {
            window.Alpine.morph(fromEl, toEl);
            return false;
          }
          return true;
        },
      },
    });

    if (this.activeSectionId) {
      const el = document.querySelector(`[${ATTRS.SectionId}="${this.activeSectionId}"]`) as HTMLElement;
      if (el) {
        this.highlightSection(el);
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
  document.dispatchEvent(new CustomEvent(EVENTS.prefix + EVENTS.EDITOR_INITIALIZED));
});

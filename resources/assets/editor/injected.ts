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

enum Attr {
  SectionId = 'data-section-id',
  SectionType = 'data-section-type',
  VisualInit = 'data-visualized',
  SectionName = 'data-section-name',
}

const EVENTS = {
  prefix: 'visual:',
  initialize: 'initialize',
  editorInitialized: 'editor:init',
  settingUpdate: 'setting:update',
  usedColors: 'usedColors',
  sectionHighlight: 'section:highlight',
  sectionSelect: 'section:select',
  clearActiveSection: 'clearActiveSection',
  sectionsOrder: 'sectionsOrder',
  refreshPreview: 'refresh',
  moveSectionUp: 'section:move-up',
  moveSectionDown: 'section:move-down',
  editSection: 'section:edit',
  toggleSection: 'section:toggle',
  removeSection: 'section:remove',
} as const;

type Unsubscribe = () => void;

class VisualObject {
  inDesignMode = true;
  inPreviewMode = false;

  on<T>(event: keyof typeof EVENTS | string, handler: (detail: T) => void): Unsubscribe {
    const name = event.startsWith(EVENTS.prefix) ? event : EVENTS.prefix + event;
    const wrapped = (e: Event) => handler((e as CustomEvent<T>).detail);

    document.addEventListener(name, wrapped);

    return () => document.removeEventListener(name, wrapped);
  }

  _dispatch<T>(event: string, detail: T) {
    const name = EVENTS.prefix + event;
    document.dispatchEvent(new CustomEvent(name, { detail }));
  }

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
    }>(EVENTS.settingUpdate, ({ data, skipRefresh }) => {
      const { section, block, settingId, settingValue } = data;

      if (!section || section.type !== sectionType) {
        return;
      }

      const container = document.querySelector(`[${Attr.SectionId}="${section.id}"]`) as HTMLElement;
      if (!container) {
        return;
      }

      let config: LiveUpdateOptions | undefined;

      if (block) {
        config = mappings.blocks?.[block.type]?.[settingId];
      } else {
        config = mappings.section?.[settingId];
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
        (config.handler as Function)(targetEl, value);
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

  private activeSectionId: string | null = null;
  private sectionsOrder: string[] = [];
  private hoverDebounce = 0;
  private resizeObserver?: ResizeObserver;

  init() {
    this.sectionOverlay = document.querySelector('#section-overlay') as HTMLDivElement;
    this.sectionLabel = document.querySelector('#label') as HTMLElement;

    this.moveUpBtn = this.sectionOverlay.querySelector('#move-up') as HTMLButtonElement;
    this.moveDownBtn = this.sectionOverlay.querySelector('#move-down') as HTMLButtonElement;
    this.editBtn = this.sectionOverlay.querySelector('#edit') as HTMLButtonElement;
    this.disableBtn = this.sectionOverlay.querySelector('#disable') as HTMLButtonElement;
    this.removeBtn = this.sectionOverlay.querySelector('#remove') as HTMLButtonElement;

    this.moveUpBtn.addEventListener('click', () => this.postMessage(EVENTS.moveSectionUp, this.activeSectionId));
    this.moveDownBtn.addEventListener('click', () => this.postMessage(EVENTS.moveSectionDown, this.activeSectionId));
    this.editBtn.addEventListener('click', () => this.postMessage(EVENTS.editSection, this.activeSectionId));
    this.disableBtn.addEventListener('click', () => this.postMessage(EVENTS.toggleSection, this.activeSectionId));
    this.removeBtn.addEventListener('click', () => this.postMessage(EVENTS.removeSection, this.activeSectionId));

    this.attachMouseEvents();

    this.postMessage(EVENTS.initialize, {
      themeData: window.themeData,
      templates: window.templates,
      settingsSchema: window.settingsSchema,
    });

    this.sectionsOrder = window.themeData.sectionsOrder;

    window.addEventListener('message', ({ data }) => this.handleMessage(data));

    this.extractUsedColors();
  }

  private attachMouseEvents() {
    document.addEventListener(
      'mouseover',
      (e) => {
        if (!(e.target instanceof Element)) {
          return;
        }

        const section = (e.target as Element).closest(`[${Attr.SectionType}]`) as HTMLElement;

        if (!section) {
          return;
        }

        if (section.dataset.sectionId !== this.activeSectionId) {
          this.debounceHighlight(section);
        }
      },
      { passive: true }
    );

    document.addEventListener(
      'mouseleave',
      (e) => {
        if (!(e.target instanceof Element)) {
          return;
        }

        const section = (e.target as Element).closest(`[${Attr.SectionType}]`) as HTMLElement;

        if (!section) {
          return;
        }

        const toEl = (e as MouseEvent).relatedTarget;

        if (
          section.dataset.sectionId === this.activeSectionId &&
          (!toEl || !this.sectionOverlay.contains(toEl as Node))
        ) {
          this.clearActiveSection();
        }
      },
      { passive: true }
    );
  }

  private debounceHighlight(section: HTMLElement) {
    clearTimeout(this.hoverDebounce);

    this.hoverDebounce = window.setTimeout(() => {
      this.highlightSection(section);

      const rect = section.getBoundingClientRect();

      if (rect.top < 0 || rect.bottom > window.innerHeight) {
        section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }, 50);
  }

  private highlightSection(section: HTMLElement) {
    this.activeSectionId = section.dataset.sectionId as string;

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

      if (position > 0) {
        this.moveUpBtn.style.display = 'inline';
      } else {
        this.moveUpBtn.style.display = 'none';
      }

      if (position > 0 && position < this.sectionsOrder.length - 1) {
        this.moveDownBtn.style.display = 'inline';
      } else {
        this.moveDownBtn.style.display = 'none';
      }

      if (this.resizeObserver) {
        this.resizeObserver.disconnect();
      }

      this.resizeObserver = new ResizeObserver(() => this.highlightSection(section));
      this.resizeObserver.observe(section);
    });
  }

  private clearActiveSection() {
    this.activeSectionId = null;
    this.sectionOverlay.style.display = 'none';

    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
      this.resizeObserver = undefined;
    }
  }

  private postMessage(type: string, data: any) {
    window.parent.postMessage({ type, data }, window.origin);
  }

  private handleMessage({ type, data, messageId }: { type: string; data: any; messageId?: string }) {
    switch (type) {
      case EVENTS.sectionHighlight:
        const sectionToHighlight = document.querySelector(`[${Attr.SectionId}="${data}"]`) as HTMLElement;

        if (sectionToHighlight) {
          this.highlightSection(sectionToHighlight);
        }
        break;

      case EVENTS.sectionSelect:
        if (this.activeSectionId === data) {
          return;
        }

        const sectionToSelect = document.querySelector(`[${Attr.SectionId}="${data}"]`) as HTMLElement;

        if (sectionToSelect) {
          this.highlightSection(sectionToSelect);
          window.Visual._dispatch(EVENTS.sectionSelect, {
            section: {
              id: sectionToSelect.dataset.sectionId,
              type: sectionToSelect.dataset.sectionType,
            },
          });
        }
        break;

      case EVENTS.clearActiveSection:
        this.clearActiveSection();
        break;

      case EVENTS.sectionsOrder:
        this.sectionsOrder = data;
        break;

      case EVENTS.refreshPreview:
        this.refreshPreviewer(data);
        break;

      case EVENTS.settingUpdate:
        let skipRefresh = false;

        window.Visual._dispatch(EVENTS.settingUpdate, {
          data,
          skipRefresh: () => (skipRefresh = true),
        });

        if (this.activeSectionId) {
          this.highlightSection(document.querySelector(`[${Attr.SectionId}="${this.activeSectionId}"]`) as HTMLElement);
        }

        if (messageId) {
          window.parent.postMessage({ messageId, skipRefresh }, window.origin);
        }
        break;

      default:
        window.Visual._dispatch(type, data);
    }
  }

  private refreshPreviewer(html: string) {
    const newDoc = new DOMParser().parseFromString(html, 'text/html');

    Idiomorph.morph(document.documentElement, newDoc.documentElement, {
      callbacks: {
        beforeNodeMorphed(fromEl: Element, toEl: Element) {
          // @ts-ignore
          if (typeof fromEl._x_dataStack !== 'undefined' && typeof window.Alpine.morph !== 'undefined') {
            window.Alpine.morph(fromEl, toEl);
            return false;
          }

          return true;
        },
      },
    });

    if (this.activeSectionId) {
      const activeEl = document.querySelector(`[${Attr.SectionId}="${this.activeSectionId}"]`) as HTMLElement;
      if (activeEl) this.highlightSection(activeEl);
    }
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

    const top = Array.from(counts.entries())
      .sort((a, b) => b[1] - a[1])
      .slice(0, limit)
      .map(([color]) => color);

    this.postMessage(EVENTS.usedColors, top);
  }
}

window.addEventListener('DOMContentLoaded', () => {
  const editor = new ThemeEditor();
  editor.init();
  document.dispatchEvent(new CustomEvent(EVENTS.prefix + EVENTS.editorInitialized));
});

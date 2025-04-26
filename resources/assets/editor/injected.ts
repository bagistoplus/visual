import { Idiomorph } from 'idiomorph/dist/idiomorph.esm.js';

window.addEventListener('DOMContentLoaded', function () {
  const editor = new ThemeEditor();
  editor.init();
});

class ThemeEditor {
  sectionOverlay!: HTMLDivElement;
  sectionLabel!: HTMLElement;
  activeSectionId: string | null = null;
  sectionsOrder: string[] = [];

  init() {
    this.sectionOverlay = document.querySelector('#section-overlay') as HTMLDivElement;
    this.sectionLabel = document.querySelector('#label') as HTMLElement;

    window.addEventListener('message', ({ data }) => {
      this.handleMessage(data);
    });

    this.sectionOverlay.querySelector('#move-up')?.addEventListener('click', this.moveActiveSectionUp.bind(this));
    this.sectionOverlay.querySelector('#move-down')?.addEventListener('click', this.moveActiveSectionDown.bind(this));
    this.sectionOverlay.querySelector('#edit')?.addEventListener('click', this.editActiveSection.bind(this));
    this.sectionOverlay.querySelector('#disable')?.addEventListener('click', this.disableActiveSection.bind(this));
    this.sectionOverlay.querySelector('#remove')?.addEventListener('click', this.removeActiveSection.bind(this));

    this.attachMouseEventsToSections();

    this.postMessage('initialize', {
      themeData: window.themeData,
      templates: window.templates,
      settingsSchema: window.settingsSchema,
    });

    this.sectionsOrder = window.themeData.sectionsOrder;

    this.extractUsedColors();
  }

  attachMouseEventsToSections() {
    document.querySelectorAll('[data-section-type]').forEach((section) => {
      if (section.hasAttribute('data-visual-initialized')) {
        return;
      }

      section.addEventListener('mouseover', this.onSectionHover.bind(this, section as HTMLElement));

      section.addEventListener('mouseleave', ((event: Event) => {
        // @ts-ignore
        if (event.toElement && this.sectionOverlay.contains(event.toElement)) {
          return;
        }

        this.onSectionBlur.call(this, section as HTMLElement);
      }) as EventListener);

      section.setAttribute('data-visual-initialized', 'true');
    });
  }

  highlightSection(section: HTMLElement) {
    this.activeSectionId = section.dataset.sectionId as string;

    this.sectionOverlay.style.display = 'block';
    this.sectionOverlay.style.width = section.offsetWidth + 'px';
    this.sectionOverlay.style.height = section.offsetHeight + 'px';
    this.sectionOverlay.style.left = section.offsetLeft + 'px';
    this.sectionOverlay.style.top = section.offsetTop + 'px';

    const sectionType = section.dataset.sectionType as string;
    this.sectionLabel.textContent = section.dataset.sectionName as string;

    (this.sectionOverlay.querySelector('#move-up') as HTMLButtonElement).style.display = 'none';
    (this.sectionOverlay.querySelector('#move-down') as HTMLButtonElement).style.display = 'none';

    const position = this.sectionsOrder.indexOf(this.activeSectionId);
    if (position > 0) {
      (this.sectionOverlay.querySelector('#move-up') as HTMLButtonElement).style.display = 'inline';
    }

    if (position < this.sectionsOrder.length - 1) {
      (this.sectionOverlay.querySelector('#move-down') as HTMLButtonElement).style.display = 'inline';
    }
  }

  clearActiveSection() {
    this.activeSectionId = null;
    this.sectionOverlay.style.display = 'none';
  }

  onSectionHover(section: HTMLElement) {
    if (this.activeSectionId === section.dataset.sectionId) {
      return;
    }

    this.highlightSection(section);
  }

  onSectionBlur(section: HTMLElement) {
    if (this.activeSectionId === section.dataset.sectionId) {
      this.clearActiveSection();
    }
  }

  moveActiveSectionUp() {
    this.postMessage('moveSectionUp', this.activeSectionId);
  }

  moveActiveSectionDown() {
    this.postMessage('moveSectionDown', this.activeSectionId);
  }

  editActiveSection() {
    this.postMessage('editSection', this.activeSectionId);
  }

  disableActiveSection() {
    this.postMessage('toggleSection', this.activeSectionId);
  }

  removeActiveSection() {
    this.postMessage('removeSection', this.activeSectionId);
  }

  refreshPreviewer(html: string) {
    const newDocument = new DOMParser().parseFromString(html, 'text/html');

    // notify section unload

    Idiomorph.morph(document.documentElement, newDocument.documentElement, {
      callbacks: {
        beforeNodeMorphed(fromEl: Element, toEl: Element) {
          // @ts-ignore
          if (typeof fromEl._x_dataStack !== 'undefined' && typeof window.Alpine.morph !== 'undefined') {
            // @ts-ignore
            window.Alpine.morph(fromEl, toEl);

            return false;
          }

          return true;
        },
      },
    });

    this.attachMouseEventsToSections();

    // notify section load
  }

  postMessage(type: string, data: any) {
    window.parent.postMessage({ type, data }, window.origin);
  }

  handleMessage({ type, data }: { type: string; data: unknown }) {
    switch (type) {
      case 'highlightSection':
        const section = document.querySelector(`[data-section-id="${data}"]`) as HTMLElement;
        if (section) {
          this.highlightSection(section);
          section.scrollIntoView({ behavior: 'smooth' });
        }
        break;
      case 'clearActiveSection':
        this.clearActiveSection();
        break;
      case 'sectionsOrder':
        this.sectionsOrder = data as string[];
        break;
      case 'refresh':
        this.refreshPreviewer(data as string);
        break;
    }
  }

  extractUsedColors(limit = 6) {
    const colorCount = new Map<string, number>();

    document.querySelectorAll('*').forEach((el) => {
      const styles = getComputedStyle(el);

      const backgroundColor = styles.backgroundColor;
      const textColor = styles.color;

      const countColor = (color: string) => {
        if (color !== 'transparent' && color !== 'rgba(0, 0, 0, 0)' && color !== '') {
          colorCount.set(color, (colorCount.get(color) || 0) + 1);
        }
      };

      countColor(backgroundColor);
      countColor(textColor);
    });

    const sortedColors = Array.from(colorCount.entries())
      .sort((a, b) => b[1] - a[1])
      .map((entry) => entry[0]);

    const mostlyUsedColors = sortedColors.slice(0, limit);

    this.postMessage('usedColors', mostlyUsedColors);
  }
}

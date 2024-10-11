import { useNProgress } from '@vueuse/integrations/useNProgress'
import { acceptHMRUpdate, defineStore } from "pinia";
import setValue from "lodash/set";
import getValue from "lodash/get";
import debounce from "lodash/debounce";
import { v4 as uuidv4 } from "uuid";

import type { Block, Section, ThemeData } from "./types";

export const useStore = defineStore('main', () => {
  let availableSections: Record<string, Section> = {};
  let previewIframe: HTMLIFrameElement | null = null;
  const nprogress = useNProgress();

  const themeData = ref<ThemeData>({
    url: '',
    channel: '',
    locale: '',
    template: '',
    hasStaticContent: false,
    sectionsOrder: [],
    beforeContentSectionsOrder: [],
    afterContentSectionsOrder: [],
    sectionsData: {}
  });
  const activeSectionId = ref<string|null>(null);

  const contentSectionsOrder = computed(() => themeData.value.sectionsOrder);
  const contentSections = computed(() => {
    return contentSectionsOrder.value.map(
      (id: string) => themeData.value?.sectionsData[id]
    );
  });

  const beforeContentSections = computed(() => {
    return themeData.value.beforeContentSectionsOrder.map(
      (id: string) => themeData.value.sectionsData[id]
    );
  });

  const persistThemeData = debounce(() => {
    const headers = new Headers();
    headers.append('content-type', 'application/json');
    headers.append(
      'X-CSRF-Token',
      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') as string
    );

    nprogress.start();

    fetch(window.ThemeEditor.routes.persistTheme, {
      headers,
      method: 'post',
      body: JSON.stringify(themeData.value)
    }).then(res => res.text())
      .then(html => {
        nprogress.done();
        refreshPreviewer(html);
      })
      .catch(e => {
        nprogress.done();
      });
  }, 500);

  function notifyPreviewIframe(type: string, data?: any) {
    previewIframe?.contentWindow?.postMessage({
      type,
      data: JSON.parse(JSON.stringify(data))
    }, window.origin);
  }

  function refreshPreviewer(html: string) {
    notifyPreviewIframe("refresh", html);
  }

  function setPreviewIframe(iframe: HTMLIFrameElement) {
    previewIframe = iframe;
  }

  function setThemeData(data: ThemeData) {
    for (const [id, section] of Object.entries(data.sectionsData)) {
      if (section.blocksOrder.length === 0) {
        section.blocks = {}
      }
    }

    themeData.value = data;
  }

  function setAvailableSections(sections: Record<string, Section>) {
    availableSections = sections;
  }

  function getThemeDataValue(keyPath: string): unknown {
    return getValue(themeData.value, keyPath);
  }

  function updateThemeDataValue(keyPath: string, value: unknown) {
    setValue(themeData.value, keyPath, value);
    persistThemeData();
  }

  function activateSection(sectionId: string) {
    activeSectionId.value = sectionId;
    notifyPreviewIframe('highlightSection', sectionId);
  }

  function deactivateSection(sectionId: string) {
    activeSectionId.value = null;
    notifyPreviewIframe('clearActiveSection', sectionId);
  }

  function setContentSectionsOrder(order: string[]) {
    updateThemeDataValue('sectionsOrder', order);
    notifyPreviewIframe('sectionsOrder', order);
    persistThemeData();
  }

  function moveSectionUp(sectionId: string) {
    const index = themeData.value.sectionsOrder.indexOf(sectionId);

    if (index === 0) {
      return;
    }

    themeData.value.sectionsOrder.splice(index, 1);
    themeData.value.sectionsOrder.splice(index - 1, 0, sectionId);

    notifyPreviewIframe('sectionsOrder', themeData.value.sectionsOrder);
    persistThemeData();
  }

  function moveSectionDown(sectionId: string) {
    const index = themeData.value.sectionsOrder.indexOf(sectionId);

    if (index === themeData.value.sectionsOrder.length - 1) {
      return;
    }

    themeData.value.sectionsOrder.splice(index, 1);
    themeData.value.sectionsOrder.splice(index + 1, 0, sectionId);

    notifyPreviewIframe('sectionsOrder', themeData.value.sectionsOrder);
    persistThemeData();
  }

  function toggleSection(sectionId: string) {
    themeData.value.sectionsData[sectionId].disabled = !themeData.value.sectionsData[sectionId].disabled;

    persistThemeData();
  }

  function removeSection(sectionId: string) {
    delete themeData.value.sectionsData[sectionId];
    themeData.value.sectionsOrder = themeData.value.sectionsOrder.filter(
      id => id !== sectionId
    );

    notifyPreviewIframe('sectionsOrder', themeData.value.sectionsOrder);
    persistThemeData();
  }

  function getSectionData(id: string) {
    return themeData.value.sectionsData[id];
  }

  function getSectionBySlug(slug: string) {
    return availableSections[slug];
  }

  function canRemoveSection(sectionId: string) {
    return themeData.value.sectionsOrder.includes(sectionId);
  }

  function addBlockToSection(sectionId: string, block: Block) {
    const sectionData = themeData.value.sectionsData[sectionId];
    const settings: Record<string, any> = {};
    const id = uuidv4();

    block.settings.forEach((setting) => {
      settings[setting.id] = setting.default;
    });

    sectionData.blocks[id] = {
      id,
      type: block.type,
      disabled: false,
      settings
    }

    sectionData.blocksOrder.push(id);

    persistThemeData();
  }

  return {
    themeData,
    availableSections,

    contentSections,
    contentSectionsOrder,
    beforeContentSections,

    setThemeData,
    setAvailableSections,
    setPreviewIframe,
    getThemeDataValue,
    updateThemeDataValue,
    activateSection,
    deactivateSection,
    setContentSectionsOrder,
    moveSectionUp,
    moveSectionDown,
    toggleSection,
    removeSection,
    getSectionData,
    getSectionBySlug,
    canRemoveSection,
    addBlockToSection
  }
});

if (import.meta.hot)
  import.meta.hot.accept(acceptHMRUpdate(useStore as any, import.meta.hot))

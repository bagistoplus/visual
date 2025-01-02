import { useNProgress } from '@vueuse/integrations/useNProgress'
import { acceptHMRUpdate, defineStore } from "pinia";
import setValue from "lodash/set";
import getValue from "lodash/get";
import debounce from "lodash/debounce";
import { v4 as uuidv4 } from "uuid";
import { History } from 'stateshot';

import type { Block, Category, CmsPage, Image, Product, Section, Setting, SettingsSchema, ThemeData } from "./types";
import { useFetchCategories, useFetchCmsPages, useFetchImages, useFetchProducts, usePublishTheme } from './api';
import { get } from 'sortablejs';

interface Models {
  categories: Record<number, Category>;
  products: Record<number, Product>;
  cmsPages: Record<number, CmsPage>;
}

export const useStore = defineStore('main', () => {
  let availableSections: Record<string, Section> = {};
  let previewIframe: HTMLIFrameElement | null = null;
  const nprogress = useNProgress();
  const history = new History();

  const settingsSchema = ref<SettingsSchema>([]);
  const usedColors = reactive<string[]>([]);
  const themeData = reactive<ThemeData>({
    url: '',
    theme: '',
    channel: '',
    locale: '',
    template: '',
    templateDataPath: '',
    hasStaticContent: false,
    sectionsOrder: [],
    beforeContentSectionsOrder: [],
    afterContentSectionsOrder: [],
    sectionsData: {},
    settings: {}
  });
  const activeSectionId = ref<string | null>(null);
  const images = reactive<Image[]>([]);
  const models = reactive<Models>({
    categories: {},
    products: {},
    cmsPages: {}
  })

  const canUndoHistory = ref(false);
  const canRedoHistory = ref(false);

  const categories = computed(() => {
    return Object.values(models.categories).map(c => ({
      ...c,
      ...c.translations.find(t => t.locale === themeData.locale)
    }))
  });

  const products = computed(() => Object.values(models.products));

  const cmsPages = computed(() => {
    return Object.values(models.cmsPages).map(page => {
      const trans = page.translations.find(t => t.locale === themeData.locale);
      if (trans) {
        page.url_key = trans.url_key;
        page.page_title = trans.page_title;
      }

      return page;
    });
  })

  const contentSectionsOrder = computed(() => themeData.sectionsOrder);
  const contentSections = computed(() => {
    return contentSectionsOrder.value.map(
      (id: string) => themeData.sectionsData[id]
    );
  });

  const beforeContentSections = computed(() => {
    return themeData.beforeContentSectionsOrder.map(
      (id: string) => themeData.sectionsData[id]
    );
  });

  const afterContentSections = computed(() => {
    return themeData.afterContentSectionsOrder.map(
      (id: string) => themeData.sectionsData[id]
    );
  });

  const persistThemeData = debounce(({ skipHistory }: {skipHistory: boolean } = { skipHistory: false }) => {
    const headers = new Headers();
    headers.append('content-type', 'application/json');
    headers.append(
      'X-CSRF-Token',
      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') as string
    );

    nprogress.start();

    if (!skipHistory) {
      history.pushSync(JSON.parse(JSON.stringify(themeData)));
      canUndoHistory.value = history.hasUndo
      canRedoHistory.value = history.hasRedo
    }

    fetch(window.ThemeEditor.routes.persistTheme, {
      headers,
      method: 'post',
      body: JSON.stringify(themeData)
    }).then(res => res.text())
      .then(html => {
        nprogress.done();
        refreshPreviewer(html);
      })
      .catch(e => {
        nprogress.done();
      });
  }, 500);

  function publishTheme() {
    nprogress.start();

    const { onFetchResponse } = usePublishTheme({theme: themeData.theme });

    onFetchResponse(() => {
      nprogress.done();
      history.reset();
      history.pushSync(JSON.parse(JSON.stringify(themeData)));

      canUndoHistory.value = history.hasUndo;
      canRedoHistory.value = history.hasRedo;
    })
  }

  function undoHistory() {
    Object.assign(themeData, history.undo().get());
    canUndoHistory.value = history.hasUndo;
    canRedoHistory.value = history.hasRedo;
    persistThemeData({ skipHistory: true });
  }

  function redoHistory() {
    Object.assign(themeData, history.redo().get());
    canUndoHistory.value = history.hasUndo;
    canRedoHistory.value = history.hasRedo;
    persistThemeData({ skipHistory: true });
  }

  function resetHistory() {
    history.reset();
    canUndoHistory.value = history.hasUndo;
    canRedoHistory.value = history.hasRedo;
  }

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
      if (section.blocks_order.length === 0) {
        section.blocks = {}
      }
    }

    Object.assign(themeData, data);
    history.pushSync(JSON.parse(JSON.stringify(themeData)));
  }

  function setAvailableSections(sections: Record<string, Section>) {
    availableSections = sections;
  }

  function setSettingsSchema(schema: SettingsSchema) {
    settingsSchema.value = schema;
  }

  function getThemeDataValue(keyPath: string | string[]): unknown {
    return getValue(themeData, keyPath);
  }

  function updateThemeDataValue(keyPath: string | string[], value: unknown) {
    setValue(themeData, keyPath, value);
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
    const index = themeData.sectionsOrder.indexOf(sectionId);

    if (index === 0) {
      return;
    }

    themeData.sectionsOrder.splice(index, 1);
    themeData.sectionsOrder.splice(index - 1, 0, sectionId);

    notifyPreviewIframe('sectionsOrder', themeData.sectionsOrder);
    persistThemeData();
  }

  function moveSectionDown(sectionId: string) {
    const index = themeData.sectionsOrder.indexOf(sectionId);

    if (index === themeData.sectionsOrder.length - 1) {
      return;
    }

    themeData.sectionsOrder.splice(index, 1);
    themeData.sectionsOrder.splice(index + 1, 0, sectionId);

    notifyPreviewIframe('sectionsOrder', themeData.sectionsOrder);
    persistThemeData();
  }

  function toggleSection(sectionId: string) {
    themeData.sectionsData[sectionId].disabled = !themeData.sectionsData[sectionId].disabled;

    persistThemeData();
  }

  function removeSection(sectionId: string) {
    delete themeData.sectionsData[sectionId];
    themeData.sectionsOrder = themeData.sectionsOrder.filter(
      id => id !== sectionId
    );

    notifyPreviewIframe('sectionsOrder', themeData.sectionsOrder);
    persistThemeData();
  }

  function getSectionData(id: string) {
    return themeData.sectionsData[id];
  }

  function getSectionBySlug(slug: string) {
    return availableSections[slug];
  }

  function getSectionBlockData(sectionId: string, blockId: string) {
    const sectionData = getSectionData(sectionId);
    return sectionData ? sectionData.blocks[blockId] : null;
  }

  function getSectionBlockByType(sectionId: string, type: string) {
    const sectionData = getSectionData(sectionId);
    if (!sectionData) {
      return null;
    }

    const section = getSectionBySlug(sectionData.type);
    return section.blocks.find(b => b.type === type);
  }

  function canRemoveSection(sectionId: string) {
    return themeData.sectionsOrder.includes(sectionId);
  }

  function addNewSection(section: Section) {
    const settings: Record<string, unknown> = {};
    const id = uuidv4();

    section.settings.forEach((setting: Setting) => {
      settings[setting.id] = setting.default;
    });

    themeData.sectionsData[id] = {
      id,
      name: section.name,
      settings,
      type: section.slug,
      blocks: {},
      blocks_order: [],
      disabled: false,
    };

    themeData.sectionsOrder.push(id);

    persistThemeData();
  }

  function addBlockToSection(sectionId: string, block: Block) {
    const sectionData = themeData.sectionsData[sectionId];
    const settings: Record<string, any> = {};
    const id = uuidv4();

    block.settings.forEach((setting) => {
      settings[setting.id] = setting.default;
    });

    sectionData.blocks[id] = {
      id,
      type: block.type,
      name: block.name,
      disabled: false,
      settings
    }

    sectionData.blocks_order.push(id);

    persistThemeData();
  }

  function toggleSectionBlock(sectionId: string, blockId: string) {
    const block = themeData.sectionsData[sectionId].blocks[blockId];

    block.disabled = !block.disabled;

    persistThemeData();
  }

  function removeSectionBlock(sectionId: string, blockId: string) {
    const section = themeData.sectionsData[sectionId];

    delete section.blocks[blockId];
    section.blocks_order = section.blocks_order.filter(id => id !== blockId);

    persistThemeData();
  }

  function searchCategories(search: string) {
    return categories.value.filter(category => new RegExp(search, 'gi').test(category.name));
  }

  function getProduct(id: number) {
    return models.products[id];
  }

  function getCmsPage(id: number) {
    let page = models.cmsPages[id];

    if (page) {
      const trans = page.translations.find(t => t.locale === themeData.locale);
      if (trans) {
        page.url_key = trans.url_key;
        page.page_title = trans.page_title;
      }
    }

    return page;
  }

  function fetchImages() {
    const { data, execute, onFetchResponse } = useFetchImages();

    if (images.length === 0) {
      execute();
    }


    onFetchResponse(() => {
      data.value.forEach((item: any) => {
        images.push({ ...item, uploading: false })
      })
    })
  }



  function fetchCategories() {
    const context = useFetchCategories();

    context.onFetchResponse(() => {
      context.data.value.data.forEach((item: Category) => {
        models.categories[item.id] = item
      });
    });

    return context;
  }

  function fetchProducts() {
    const context = useFetchProducts();

    context.onFetchResponse(() => {
      context.data.value.data.forEach((item: Product) => {
        models.products[item.id] = item
      });
    });

    function execute(params: any = {}) {
      context.execute({ locale: themeData.locale, channel: themeData.channel, ...params })
    }

    return { ...context, execute };
  }

  function fetchCmsPages() {
    const context = useFetchCmsPages();

    context.onFetchResponse(() => {
      context.data.value.forEach((item: CmsPage) => {
        models.cmsPages[item.id] = item
      });
    });

    function execute(params: any = {}) {
      context.execute({ locale: themeData.locale, channel: themeData.channel, ...params })
    }

    return { ...context, execute };
  }

  return {
    images,
    themeData,
    usedColors,
    availableSections,
    settingsSchema,
    categories,
    products,
    cmsPages,

    contentSections,
    contentSectionsOrder,
    beforeContentSections,
    afterContentSections,

    canUndoHistory,
    canRedoHistory,
    undoHistory,
    redoHistory,
    resetHistory,
    publishTheme,

    setThemeData,
    setAvailableSections,
    setPreviewIframe,
    setSettingsSchema,
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
    getSectionBlockData,
    getSectionBlockByType,
    canRemoveSection,
    addNewSection,
    addBlockToSection,
    toggleSectionBlock,
    removeSectionBlock,

    searchCategories,
    getProduct,
    getCmsPage,

    fetchImages,
    fetchCategories,
    fetchProducts,
    fetchCmsPages
  }
});

if (import.meta.hot)
  import.meta.hot.accept(acceptHMRUpdate(useStore as any, import.meta.hot))

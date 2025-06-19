import { useNProgress } from '@vueuse/integrations/useNProgress';
import { acceptHMRUpdate, defineStore } from 'pinia';
import setValue from 'lodash/set';
import getValue from 'lodash/get';
import { debounce } from 'perfect-debounce';
import { v4 as uuidv4 } from 'uuid';
import { History } from 'stateshot';

import type {
  Block,
  BlockData,
  Category,
  CmsPage,
  Image,
  PreloadedModels,
  Product,
  Section,
  SectionData,
  Setting,
  SettingsSchema,
  SettingValue,
  Template,
  ThemeData,
  ViewMode,
} from './types';
import { useFetchCategories, useFetchCmsPages, useFetchImages, useFetchProducts, usePublishTheme } from './api';
import { dir } from 'console';

interface Models {
  categories: Record<number, Category>;
  products: Record<number, Product>;
  cmsPages: Record<number, CmsPage>;
}

const previewIframe = useIframeRpc();
const { findIconById } = useIconStore();

export const useStore = defineStore('main', () => {
  let availableSections: Record<string, Section> = {};

  const nprogress = useNProgress();
  const history = new History();

  const viewMode = ref<ViewMode>('desktop');
  const templates = ref<Template[]>([]);
  const settingsSchema = ref<SettingsSchema>([]);
  const usedColors = reactive<string[]>([]);
  const themeData = reactive<ThemeData>({
    url: '/',
    theme: 'default',
    channel: 'default',
    locale: 'en',
    template: 'index',
    source: '',
    hasStaticContent: false,
    sectionsOrder: [],
    beforeContentSectionsOrder: [],
    afterContentSectionsOrder: [],
    sectionsData: {},
    settings: {},
    haveEdits: false,
  });
  const dirtySections = new Map();

  const activeSectionId = ref<string | null>(null);
  const images = reactive<Image[]>([]);
  const models = reactive<Models>({ categories: {}, products: {}, cmsPages: {} });

  const canUndoHistory = ref(false);
  const canRedoHistory = ref(false);

  const haveEdits = computed(() => themeData.haveEdits);
  const categories = computed(() => {
    return Object.values(models.categories).map((cat) => {
      const trans = cat.translations.find((t) => t.locale === themeData.locale);

      if (trans) {
        return {
          ...cat,
          name: trans.name,
          slug: trans.slug,
        };
      }

      return cat;
    });
  });

  const products = computed(() => Object.values(models.products));

  const cmsPages = computed(() => {
    return Object.values(models.cmsPages).map((page) => {
      const trans = page.translations.find((t) => t.locale === themeData.locale);

      if (trans) {
        return {
          ...page,
          url_key: trans.url_key,
          page_title: trans.page_title,
        };
      }

      return page;
    });
  });

  const contentSectionsOrder = computed(() => themeData.sectionsOrder);
  const contentSections = computed(() => {
    return contentSectionsOrder.value.map((id) => themeData.sectionsData[id]);
  });

  const beforeContentSections = computed(() => {
    return themeData.beforeContentSectionsOrder.map((id) => themeData.sectionsData[id]);
  });

  const afterContentSections = computed(() => {
    return themeData.afterContentSectionsOrder.map((id) => themeData.sectionsData[id]);
  });

  const colorSchemes = computed(() => {
    const settingId = settingsSchema.value
      .flatMap((obj) => obj.settings)
      .find((setting) => setting.type === 'color_scheme_group')?.id;

    if (!settingId) {
      return {};
    }

    return themeData.settings[settingId];
  });

  const persistThemeData = debounce(async ({ skipHistory = false, skipPreviewRefresh = false } = {}) => {
    const headers = new Headers({
      'content-type': 'application/json',
      'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') as string,
    });

    if (!skipPreviewRefresh) {
      nprogress.start();
    }

    if (!skipHistory) {
      history.pushSync(structuredClone(toRaw(themeData)));
      canUndoHistory.value = history.hasUndo;
      canRedoHistory.value = history.hasRedo;
    }

    const updatedSections = new Map(dirtySections);
    dirtySections.clear();

    try {
      const res = await fetch(window.ThemeEditor.route('persistTheme'), {
        headers,
        method: 'post',
        body: JSON.stringify({ ...themeData, updatedSections: Array.from(updatedSections.keys()) }),
      });

      if (!skipPreviewRefresh) {
        const html = await res.text();
        await previewIframe.call('refresh', { html, updatedSections }, 0);
      }
    } catch (error) {
      console.error('Failed to persistThemeData: ' + error);
    } finally {
      if (!skipPreviewRefresh) {
        nprogress.done();
      }
    }
  }, 500);

  function publishTheme() {
    nprogress.start();

    const { onFetchResponse } = usePublishTheme({ theme: themeData.theme });

    onFetchResponse(() => {
      nprogress.done();
      history.reset();
      history.pushSync(structuredClone(toRaw(themeData)));

      canUndoHistory.value = history.hasUndo;
      canRedoHistory.value = history.hasRedo;
      themeData.haveEdits = false;
    });
  }

  function setPreviewIframe(iframe: HTMLIFrameElement) {
    previewIframe.setIframe(iframe);
  }

  function setPreviewIframeReady() {
    previewIframe.markReady();
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

  // Getters
  function searchCategories(search: string) {
    return categories.value.filter((category) => new RegExp(search, 'gi').test(category.name));
  }

  function getProduct(id: number) {
    return models.products[id];
  }

  function getCategory(id: number) {
    let category = models.categories[id];

    if (category) {
      const trans = category.translations.find((t) => t.locale === themeData.locale);
      if (trans) {
        category.name = trans.name;
        category.slug = trans.slug;
      }
    }

    return category;
  }

  function getCmsPage(id: number) {
    let page = models.cmsPages[id];

    if (page) {
      const trans = page.translations.find((t) => t.locale === themeData.locale);
      if (trans) {
        page.url_key = trans.url_key;
        page.page_title = trans.page_title;
      }
    }

    return page;
  }

  // Setters
  function setThemeData(data: ThemeData) {
    for (const [id, section] of Object.entries(data.sectionsData)) {
      if (section.blocks_order.length === 0) {
        section.blocks = {};
      }
    }

    Object.assign(themeData, data);
    history.pushSync(structuredClone(data));
  }

  function setPreloadedModels(preloadedModels: PreloadedModels) {
    preloadedModels.products.forEach((product) => {
      models.products[product.id] = product;
    });

    preloadedModels.categories.forEach((cat) => {
      models.categories[cat.id] = cat;
    });

    preloadedModels.cms_pages.forEach((page) => {
      models.cmsPages[page.id] = page;
    });
  }

  function setTemplates(tpls: Template[]) {
    templates.value = tpls;
  }

  function setAvailableSections(sections: Record<string, Section>) {
    availableSections = sections;
  }

  function setSettingsSchema(schema: SettingsSchema) {
    settingsSchema.value = schema;
  }

  // Settings update
  function _parseSettingPath(path: string[] | string) {
    const parts = Array.isArray(path) ? path : path.split('.');

    if (parts[0] === 'sectionsData') {
      const sectionId = parts[1];
      const section = toRaw(themeData.sectionsData?.[sectionId]) || null;

      if (!section) {
        return { section: null, block: null, settingId: null };
      }

      if (parts[2] === 'blocks') {
        const blockId = parts[3];
        const block = toRaw(section.blocks?.[blockId]) || null;
        const settingId = parts.slice(5).join('.');

        return { section, block, settingId };
      }

      const settingId = parts.slice(3).join('.');

      return { section, block: null, settingId };
    }

    if (parts[0] === 'settings') {
      const settingId = parts.slice(1).join('.');
      return { section: null, block: null, settingId };
    }

    return { section: null, block: null, settingId: null };
  }

  function getRealSettingValue(
    value: SettingValue,
    context: { section?: SectionData; block?: BlockData; settingId?: string }
  ): any {
    const { section, block, settingId } = context;

    if (!section || !settingId) {
      return value;
    }

    const sectionConfig = window.editorConfig.sections[section.type];
    if (!sectionConfig) {
      return value;
    }

    const settings = block
      ? sectionConfig.blocks.find((b) => b.type === block.type)?.settings ?? []
      : sectionConfig.settings;

    const setting = settings.find((s) => s.id === settingId);
    if (!setting || !value) {
      return value;
    }

    switch (setting.type) {
      case 'image': {
        return String(value).startsWith('http') || String(value).startsWith('/')
          ? value
          : `${window.ThemeEditor.imagesBaseUrl()}/${value}`;
      }

      case 'product': {
        return toRaw(getProduct(value as number));
      }

      case 'category': {
        return toRaw(getCategory(value as number));
      }

      case 'cms_page': {
        return toRaw(getCmsPage(value as number));
      }

      case 'icon':
        const icon = findIconById(value as string);
        return icon?.svg;

      case 'link': {
        const strVal = String(value);

        if (!strVal.startsWith('visual://')) {
          return value;
        }

        const matches = strVal.match(/^visual:\/\/([^:]+):([^\/]+)\/(.*)?$/);
        if (!matches) {
          return value;
        }

        const [, type, , slug] = matches;
        const base = new URL(window.ThemeEditor.storefrontUrl()).origin;
        const path = type === 'cms_page' ? `page/${slug}` : slug;

        return new URL(path, base).href;
      }

      default: {
        return value;
      }
    }
  }

  async function updateThemeDataValue(keyPath: string | string[], value: unknown) {
    setValue(themeData, keyPath, value);

    const context = _parseSettingPath(keyPath);
    let skipPreviewRefresh = false;

    if (context.settingId) {
      const response = await previewIframe.call('setting:updated', {
        ...context,
        settingValue: getRealSettingValue(toRaw(value) as SettingValue, context as any),
      });

      if (response?.skipRefresh) {
        skipPreviewRefresh = true;
      }
    }

    if (context.section && !skipPreviewRefresh) {
      await previewIframe.call('section:updating', { section: context.section, block: context.block }, 0);
    }

    if (context.section) {
      dirtySections.set(context.section.id, context);
    }

    await persistThemeData({ skipPreviewRefresh });
  }

  function getThemeDataValue(keyPath: string | string[]): unknown {
    return getValue(themeData, keyPath);
  }

  // Section operations
  function moveSectionUp(sectionId: string) {
    const idx = themeData.sectionsOrder.indexOf(sectionId);

    if (idx === 0) {
      return;
    }
    themeData.sectionsOrder.splice(idx, 1);
    themeData.sectionsOrder.splice(idx - 1, 0, sectionId);

    persistThemeData({ skipPreviewRefresh: true });
    previewIframe.call('reordering', { order: toRaw(themeData.sectionsOrder), sectionId }, 0);
    previewIframe.call('sectionsOrder', toRaw(themeData.sectionsOrder));
  }

  function moveSectionDown(sectionId: string) {
    const idx = themeData.sectionsOrder.indexOf(sectionId);

    if (idx === themeData.sectionsOrder.length - 1) {
      return;
    }

    themeData.sectionsOrder.splice(idx, 1);
    themeData.sectionsOrder.splice(idx + 1, 0, sectionId);

    persistThemeData({ skipPreviewRefresh: true });
    previewIframe.call('reordering', { order: toRaw(themeData.sectionsOrder), sectionId }, 0);
    previewIframe.call('sectionsOrder', toRaw(themeData.sectionsOrder));
  }

  function toggleSection(sectionId: string) {
    const section = themeData.sectionsData[sectionId];
    const disabled = !section.disabled;

    themeData.sectionsData[sectionId].disabled = disabled;

    if (disabled) {
      previewIframe.call('section:removed', toRaw(themeData.sectionsData[sectionId]), 0);
    }

    dirtySections.set(sectionId, {
      section: toRaw(section),
      block: null,
      settingId: null,
      position: [
        ...themeData.beforeContentSectionsOrder,
        ...themeData.sectionsOrder,
        ...themeData.afterContentSectionsOrder,
      ].indexOf(sectionId),
    });

    persistThemeData({ skipPreviewRefresh: disabled });
  }

  function getSectionData(id: string) {
    return themeData.sectionsData[id];
  }

  function getSectionBySlug(slug: string) {
    return availableSections[slug];
  }

  function removeSection(sectionId: string) {
    const section = themeData.sectionsData[sectionId];

    delete themeData.sectionsData[sectionId];
    themeData.sectionsOrder = themeData.sectionsOrder.filter((id) => id !== sectionId);

    previewIframe.call('sectionsOrder', toRaw(themeData.sectionsOrder));
    previewIframe.call('section:removed', toRaw(section));

    persistThemeData({ skipPreviewRefresh: true });
  }

  async function addNewSection(section: Section) {
    const settings: Record<string, SettingValue> = {};
    const id = uuidv4();

    section.settings.forEach(({ id, default: defaultValue }) => {
      settings[id] = section.default.settings?.[id] ?? defaultValue;
    });

    const sectionData: SectionData = {
      id,
      name: section.name,
      settings,
      type: section.slug,
      blocks: {},
      blocks_order: [],
      disabled: false,
    };

    themeData.sectionsData[id] = sectionData;
    themeData.sectionsOrder.push(id);

    // set default blocks
    if (Array.isArray(section.default.blocks)) {
      section.default.blocks.forEach((blockData) => {
        const block = section.blocks.find(({ type }) => type === blockData.type);

        if (block) {
          addBlockToSection(id, block, blockData.settings, true);
        }
      });
    }

    dirtySections.set(id, {
      section: toRaw(sectionData),
      block: null,
      settingId: null,
      position: themeData.beforeContentSectionsOrder.length + themeData.sectionsOrder.length - 1,
    });
    await persistThemeData();
    previewIframe.call('section:added', { section: toRaw(themeData.sectionsData[id]) });
  }

  async function addBlockToSection(
    sectionId: string,
    block: Block,
    defaults: Record<string, any> = {},
    skipNotify = false
  ) {
    const sectionData = themeData.sectionsData[sectionId];
    const settings: Record<string, any> = {};
    const id = uuidv4();

    block.settings.forEach((setting) => {
      settings[setting.id] = defaults[setting.id] || setting.default;
    });

    sectionData.blocks[id] = {
      id,
      type: block.type,
      name: block.name,
      disabled: false,
      settings,
    };

    sectionData.blocks_order.push(id);

    if (!skipNotify) {
      const context = { section: toRaw(sectionData), block: toRaw(sectionData.blocks[id]) };
      await previewIframe.call('section:updating', context, 0);

      dirtySections.set(sectionData.id, context);

      persistThemeData();
    }
  }

  function toggleSectionBlock(sectionId: string, blockId: string) {
    const section = themeData.sectionsData[sectionId];
    const block = section.blocks[blockId];

    block.disabled = !block.disabled;

    dirtySections.set(sectionId, { section: toRaw(section), block: toRaw(block), settingId: null });
    persistThemeData();
  }

  async function removeSectionBlock(sectionId: string, blockId: string) {
    const section = themeData.sectionsData[sectionId];

    delete section.blocks[blockId];
    section.blocks_order = section.blocks_order.filter((id) => id !== blockId);

    dirtySections.set(sectionId, { section: toRaw(section), block: null, settingId: null });
    await persistThemeData();
  }

  function activateSection(sectionId: string) {
    activeSectionId.value = sectionId;
    previewIframe.call('section:highlight', sectionId);
  }

  function deactivateSection(sectionId: string) {
    activeSectionId.value = null;
    previewIframe.call('section:unhighlight', sectionId);
  }

  function selectSection(sectionId: string) {
    activeSectionId.value = sectionId;
    previewIframe.call('section:select', sectionId, 0);
  }

  function deselectSection(sectionId: string) {
    activeSectionId.value = null;
    previewIframe.call('section:deselect', sectionId, 0);
  }

  function selectBlock(sectionId: string, blockId: string) {
    activeSectionId.value = sectionId;
    previewIframe.call('block:select', { sectionId, blockId }, 0);
  }

  function deselectBlock(sectionId: string, blockId: string) {
    activeSectionId.value = sectionId;
    previewIframe.call('block:deselect', { sectionId, blockId }, 0);
  }

  function setContentSectionsOrder(order: string[]) {
    themeData.sectionsOrder = order;
    persistThemeData({ skipPreviewRefresh: true });

    if (viewMode.value === 'reordering') {
      viewMode.value = 'desktop';
    }

    previewIframe.call('sectionsOrder', order);
  }

  function reorderingContentSections({ order, sectionId }: { order: string[]; sectionId: string }) {
    if (viewMode.value !== 'mobile') {
      viewMode.value = 'reordering';
    }

    previewIframe.call('reordering', {
      order: [...themeData.beforeContentSectionsOrder, ...order, ...themeData.afterContentSectionsOrder],
      sectionId,
    });
  }

  // Helpers
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
    return section?.blocks.find((b) => b.type === type);
  }

  function canRemoveSection(sectionId: string) {
    return themeData.sectionsOrder.includes(sectionId);
  }

  // Data fetchers
  function fetchImages() {
    const { data, execute, onFetchResponse } = useFetchImages();

    if (images.length === 0) {
      execute();
    }

    onFetchResponse(() => {
      data.value.forEach((item: any) => {
        images.push({ ...item, uploading: false });
      });
    });
  }

  function fetchCategories() {
    const context = useFetchCategories();

    context.onFetchResponse(() => {
      context.data.value.data.forEach((item: Category) => {
        models.categories[item.id] = item;
      });
    });

    function execute(params: any = {}) {
      context.execute({ locale: themeData.locale, channel: themeData.channel, ...params });
    }

    return { ...context, execute };
  }

  function fetchProducts() {
    const context = useFetchProducts();

    context.onFetchResponse(() => {
      context.data.value.data.forEach((item: Product) => {
        models.products[item.id] = item;
      });
    });

    function execute(params: any = {}) {
      context.execute({ locale: themeData.locale, channel: themeData.channel, ...params });
    }

    return { ...context, execute };
  }

  function fetchCmsPages() {
    const context = useFetchCmsPages();

    context.onFetchResponse(() => {
      context.data.value.forEach((item: CmsPage) => {
        models.cmsPages[item.id] = item;
      });
    });

    function execute(params: any = {}) {
      context.execute({ locale: themeData.locale, channel: themeData.channel, ...params });
    }

    return { ...context, execute };
  }

  return {
    haveEdits,
    viewMode,
    images,
    themeData,
    templates,
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
    colorSchemes,

    canUndoHistory,
    canRedoHistory,
    undoHistory,
    redoHistory,
    resetHistory,
    publishTheme,
    setPreviewIframeReady,

    setThemeData,
    setTemplates,
    setPreloadedModels,
    setAvailableSections,
    setPreviewIframe,
    setSettingsSchema,
    getThemeDataValue,
    updateThemeDataValue,
    activateSection,
    deactivateSection,
    selectSection,
    deselectSection,
    selectBlock,
    deselectBlock,
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
    reorderingContentSections,

    searchCategories,
    getProduct,
    getCategory,
    getCmsPage,

    fetchImages,
    fetchCategories,
    fetchProducts,
    fetchCmsPages,
  };
});

if (import.meta.hot) import.meta.hot.accept(acceptHMRUpdate(useStore as any, import.meta.hot));

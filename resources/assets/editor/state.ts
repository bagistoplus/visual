import { Category, Channel, CmsPage, Image, Product, Template, Theme } from './types';

export const VISUAL_EDITOR_STATE = Symbol('VISUAL_EDITOR_STATE');

export interface State {
  channels: Channel[];
  channel: string;
  locale: string;
  theme: Theme | null;
  templates: Template[];
  pageData: {
    url: string;
    template: string;
    sources: string;
    settings?: Record<string, any>;
  } | null;
  images: Image[];
  categories: Map<number, Category>;
  products: Map<number, Product>;
  cmsPages: Map<number, CmsPage>;
  haveEdits: boolean;
}

let state: State | null = null;

export function createState(defaults: Partial<State> = {}): State {
  state = reactive({
    channels: defaults.channels || [],
    channel: 'default',
    locale: 'en',
    theme: defaults.theme || null,
    templates: defaults.templates || [],
    pageData: null,
    images: defaults.images || [],
    categories: defaults.categories || new Map(),
    products: defaults.products || new Map(),
    cmsPages: defaults.cmsPages || new Map(),
    haveEdits: defaults.haveEdits || false,
  });

  return state as State;
}

export function useState() {
  if (!state) {
    throw new Error('State not initialized. Make sure to call createState first.');
  }

  function getCategory(id: number): Category | undefined {
    const category = state!.categories.get(id);

    if (category && category.translations) {
      const trans = category.translations.find((t: any) => t.locale === state!.locale);
      if (trans) {
        return {
          ...category,
          name: trans.name,
          slug: trans.slug,
        };
      }
    }

    return category;
  }

  function getCategories(): Category[] {
    return Array.from(state!.categories.values()).map((category) => {
      if (category.translations) {
        const trans = category.translations.find((t: any) => t.locale === state!.locale);
        if (trans) {
          return {
            ...category,
            name: trans.name,
            slug: trans.slug,
          };
        }
      }
      return category;
    });
  }

  function getProduct(id: number): Product | undefined {
    return state!.products.get(id);
  }

  function getProducts(): Product[] {
    return Array.from(state!.products.values());
  }

  function getCmsPage(id: number): CmsPage | undefined {
    const page = state!.cmsPages.get(id);

    if (page && page.translations) {
      const trans = page.translations.find((t: any) => t.locale === state!.locale);
      if (trans) {
        return {
          ...page,
          url_key: trans.url_key,
          page_title: trans.page_title,
        };
      }
    }

    return page;
  }

  function getCmsPages(): CmsPage[] {
    return Array.from(state!.cmsPages.values()).map((page) => {
      if (page.translations) {
        const trans = page.translations.find((t: any) => t.locale === state!.locale);
        if (trans) {
          return {
            ...page,
            url_key: trans.url_key,
            page_title: trans.page_title,
          };
        }
      }
      return page;
    });
  }

  return {
    state,
    ...toRefs(state),
    currentTemplate: computed(() => {
      const templateName = state!.pageData?.template;
      return state!.templates.find((t) => t.template === templateName) || null;
    }),
    getCategory,
    getCategories,
    getProduct,
    getProducts,
    getCmsPage,
    getCmsPages,
  };
}

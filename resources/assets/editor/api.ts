import { UpdatesEvent } from '@craftile/types';
import { useHttpClient } from './composables/http';
import { useState } from './state';
import { canonicalizePage, canonicalizeUpdates } from './utils/resolvedTranslationRefs';

export function persistUpdates(updates: UpdatesEvent) {
  const { state } = useState();
  const { post } = useHttpClient();

  const request = post(window.editorConfig.routes.persistUpdates, {
    theme: state.theme?.code,
    channel: state.channel || window.editorConfig.defaultChannel,
    locale: state.locale || window.editorConfig.editorLocale,
    template: {
      url: state.pageData?.url || '',
      name: state.pageData?.template || 'index',
      sources: state.pageData?.sources,
    },
    updates: canonicalizeUpdates(updates),
  }).text();

  return request;
}

export function persistThemeSettings(updates: Record<string, any>) {
  const { state } = useState();
  const { post } = useHttpClient();

  const request = post(window.editorConfig.routes.persistThemeSettings, {
    theme: state.theme!.code,
    channel: state.channel || window.editorConfig.defaultChannel,
    locale: state.locale || window.editorConfig.editorLocale,
    template: {
      url: state.pageData?.url || '',
      name: state.pageData?.template || 'index',
      sources: state.pageData?.sources,
    },
    updates,
  }).text();

  request.onError((error) => {
    console.error('Failed to persist theme settings:', error);
  });

  return request;
}

export function publishTheme(pageData?: any) {
  const { state } = useState();
  const { post } = useHttpClient();

  const request = post(window.editorConfig.routes.publishTheme, {
    theme: state.theme!.code,
    channel: state.channel || window.editorConfig.defaultChannel,
    locale: state.locale || window.editorConfig.editorLocale,
    template: state.pageData?.template || 'index',
    page: canonicalizePage(pageData),
  });

  request.onError((error) => {
    console.error('Failed to publish theme:', error);
  });

  return request;
}

export function discardTheme() {
  const { state } = useState();
  const { post } = useHttpClient();

  const request = post(window.editorConfig.routes.discardTheme, {
    theme: state.theme!.code,
    channel: state.channel || window.editorConfig.defaultChannel,
  });

  request.onError((error) => {
    console.error('Failed to discard edits:', error);
  });

  return request;
}

export function uploadSectionMedia(section: number, file: File) {
  const { state } = useState();
  const { postFormData } = useHttpClient();

  const payload = new FormData();

  payload.append('theme', state.theme!.code);
  payload.append('section', String(section));
  payload.append('file', file);

  return postFormData<{ path: string; type: string; url: string }>(
    window.editorConfig.routes.uploadSectionMedia,
    payload
  );
}

export interface SectionMedia {
  path: string;
  url: string;
  section: number;
}

/**
 * Every file the theme's sections hold. A section can be pointed at one another
 * section already uses, which is what makes this a library rather than a list of
 * what this section uploaded.
 */
export function listSectionMedia() {
  const { state } = useState();
  const { get } = useHttpClient();

  const url = `${window.editorConfig.routes.listSectionMedia}?theme=${encodeURIComponent(state.theme!.code)}`;

  return get<SectionMedia[]>(url);
}

/**
 * Keep a copy of a file among the given section's own media.
 *
 * Bagisto deletes every file a section's options stop naming, so a section
 * pointed at a file another one owns would lose it on that section's next save.
 */
export function copySectionMedia(section: number, path: string) {
  const { state } = useState();
  const { post } = useHttpClient();

  return post<{ path: string; url: string }>(window.editorConfig.routes.copySectionMedia, {
    theme: state.theme!.code,
    section,
    path,
  });
}

export function createTemplate(payload: { type: string; name: string; basedOn?: string | null }) {
  const { state } = useState();
  const { post } = useHttpClient();

  return post(window.editorConfig.routes.createTemplate, {
    theme: state.theme!.code,
    channel: state.channel || window.editorConfig.defaultChannel,
    locale: state.locale || window.editorConfig.editorLocale,
    ...payload,
  });
}

export function fetchCategories() {
  const { state } = useState();
  const url = ref('/api/categories');
  const { get } = useHttpClient();

  const request = get(url);

  request.onSuccess((data: any) => {
    const categories = data?.data || [];
    categories.forEach((category: any) => {
      state.categories.set(category.id, category);
    });
  });

  request.onError((error) => {
    console.error('Failed to fetch categories:', error);
  });

  function execute(params: { channel?: string; locale?: string; search?: string } = {}) {
    const newUrl = new URL('/api/categories', window.location.origin);

    newUrl.searchParams.append('channel', params.channel || state.channel);
    newUrl.searchParams.append('locale', params.locale || state.locale);

    if (params.search) {
      newUrl.searchParams.append('name', params.search);
    }

    url.value = newUrl.href;

    return request.execute();
  }

  return { ...request, execute };
}

export function fetchProducts() {
  const { state } = useState();
  const url = ref('/api/products');
  const { get } = useHttpClient();

  const request = get(url);

  request.onSuccess((data: any) => {
    const products = data?.data || [];
    products.forEach((product: any) => {
      state.products.set(product.id, product);
    });
  });

  request.onError((error) => {
    console.error('Failed to fetch products:', error);
  });

  function execute(params: { channel?: string; locale?: string; search?: string } = {}) {
    const newUrl = new URL('/api/products', window.location.origin);

    newUrl.searchParams.append('channel', params.channel || state.channel);
    newUrl.searchParams.append('locale', params.locale || state.locale);

    if (params.search) {
      newUrl.searchParams.append('name', params.search);
    }

    url.value = newUrl.href;

    return request.execute();
  }

  return { ...request, execute };
}

export function fetchCmsPages() {
  const { state } = useState();
  const url = ref(window.editorConfig.routes.getCmsPages);
  const { get } = useHttpClient();

  const request = get(url);

  request.onSuccess((data: any) => {
    const pages = data || [];
    pages.forEach((page: any) => {
      state.cmsPages.set(page.id, page);
    });
  });

  request.onError((error) => {
    console.error('Failed to fetch CMS pages:', error);
  });

  function execute(params: { channel?: string; locale?: string; search?: string } = {}) {
    const baseUrl = window.editorConfig.routes.getCmsPages;
    const newUrl = new URL(baseUrl, window.location.origin);

    newUrl.searchParams.append('channel', params.channel || state.channel);
    newUrl.searchParams.append('locale', params.locale || state.locale);

    if (params.search) {
      newUrl.searchParams.append('title', params.search);
    }

    url.value = newUrl.href;

    return request.execute();
  }

  return { ...request, execute };
}

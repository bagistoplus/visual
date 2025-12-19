import { UpdatesEvent } from '@craftile/types';
import { useHttpClient } from './composables/http';
import { useState } from './state';

export function persistUpdates(updates: UpdatesEvent) {
  const { state } = useState();
  const { post } = useHttpClient();

  const request = post(window.editorConfig.routes.persistUpdates, {
    theme: state.theme?.code,
    channel: window.editorConfig.defaultChannel,
    locale: window.editorConfig.editorLocale,
    template: {
      url: state.pageData?.url || '',
      name: state.pageData?.template || 'index',
      sources: state.pageData?.sources,
    },
    updates,
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
    page: pageData,
  });

  request.onError((error) => {
    console.error('Failed to publish theme:', error);
  });

  return request;
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

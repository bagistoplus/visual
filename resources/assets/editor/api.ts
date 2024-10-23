import { createFetch } from "@vueuse/core";
import { ThemeData } from "./types";

const routes = window.ThemeEditor.routes;

const useFetch = createFetch({
  options: {
    beforeFetch({ options }) {
      const headers = new Headers(options.headers || []);

      headers.append('X-CSRF-Token',
        document.querySelector('meta[name="csrf-token"]')!.getAttribute("content") as string
      )

      options.headers = headers;

      return { options };
    },
  },
  fetchOptions: {
    mode: "cors",
  },
});

export function useUploadImage(formData: FormData) {
  return useFetch(routes.uploadImage).post(formData).json();
}

export function useFetchImages() {
  return useFetch(routes.listImages, { immediate: false }).get().json();
}

export function useFetchCategories() {
  return useFetch('/api/categories').get().json();
}

export function useFetchProducts() {
  const url = ref('/api/products')
  const context = useFetch(url, { refetch: true, immediate: false }).get().json();

  function execute(params: Record<string, any>) {
    const newUrl = new URL('/api/products', window.location.origin);

    for (const [key, value] of Object.entries(params)) {
      newUrl.searchParams.append(key, value);
    }

    url.value = newUrl.href;
  }

  return { ...context, execute };
}

export function useFetchCmsPages() {
  const url = ref(window.ThemeEditor.routes.getCmsPages)
  const context = useFetch(url, { refetch: true, immediate: false }).get().json();

  function execute(params: Record<string, any>) {
    const newUrl = new URL(window.ThemeEditor.routes.getCmsPages, window.location.origin);

    for (const [key, value] of Object.entries(params)) {
      newUrl.searchParams.append(key, value);
    }

    url.value = newUrl.href;
  }

  return { ...context, execute };
}

export function usePublishTheme(data: any) {
  return useFetch(window.ThemeEditor.routes.publishTheme).post(data);
}

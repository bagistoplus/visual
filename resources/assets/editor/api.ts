import { createFetch } from "@vueuse/core";

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

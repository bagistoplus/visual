import { Ref, ref, MaybeRefOrGetter, toValue } from 'vue';
import NProgress from 'nprogress';

export interface UseFetchOptions {
  immediate?: boolean;
  responseType?: 'json' | 'text';
}

export interface UseFetchReturn<T = any, D = any> {
  data: Ref<T | null>;
  error: Ref<Error | null>;
  isFetching: Ref<boolean>;
  isFinished: Ref<boolean>;
  statusCode: Ref<number | null>;
  execute: (dataOverride?: D, throwOnFail?: boolean) => Promise<T | null>;
  abort: () => void;
  onSuccess: (fn: (data: T) => void) => void;
  onError: (fn: (error: Error) => void) => void;
  onFinish: (fn: () => void) => void;
  json: () => UseFetchReturn<T, D>;
  text: () => UseFetchReturn<string, D>;
}

export type HttpClient = {
  get<T = any>(url: MaybeRefOrGetter<string>, options?: UseFetchOptions): UseFetchReturn<T>;
  post<T = any>(url: MaybeRefOrGetter<string>, data?: any, options?: UseFetchOptions): UseFetchReturn<T, any>;
  postFormData<T = any>(
    url: MaybeRefOrGetter<string>,
    formData?: FormData,
    options?: UseFetchOptions
  ): UseFetchReturn<T, FormData>;
};

function getCsrfToken(): string {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

export function useHttpClient(): HttpClient {
  return createHttpClientInstance();
}

function createUseFetch<T = any, D = any>(
  fetcher: (signal: AbortSignal, dataOverride?: D) => Promise<Response>,
  options: UseFetchOptions = {}
): UseFetchReturn<T, D> {
  const data = ref<T | null>(null);
  const error = ref<Error | null>(null);
  const isFetching = ref(false);
  const isFinished = ref(false);
  const statusCode = ref<number | null>(null);

  let abortController: AbortController | null = null;
  const successCallbacks: Array<(data: T) => void> = [];
  const errorCallbacks: Array<(error: Error) => void> = [];
  const finishCallbacks: Array<() => void> = [];

  const execute = async (dataOverride?: D, throwOnFail = false): Promise<T | null> => {
    // Start progress bar
    NProgress.start();

    // Reset state
    isFetching.value = true;
    isFinished.value = false;
    error.value = null;

    // Create new abort controller
    abortController = new AbortController();

    try {
      const response = await fetcher(abortController.signal, dataOverride);
      statusCode.value = response.status;

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      console.log('Fetch response received:', response);
      const result = options.responseType === 'text' ? await response.text() : await response.json();
      data.value = result as T;

      // Call success callbacks
      successCallbacks.forEach((cb) => cb(result as T));

      return result as T;
    } catch (err) {
      const fetchError = err instanceof Error ? err : new Error(String(err));
      error.value = fetchError;

      // Call error callbacks
      errorCallbacks.forEach((cb) => cb(fetchError));

      if (throwOnFail) {
        throw fetchError;
      }

      return null;
    } finally {
      isFetching.value = false;
      isFinished.value = true;

      // Complete progress bar
      NProgress.done();

      // Call finish callbacks
      finishCallbacks.forEach((cb) => cb());
    }
  };

  const abort = () => {
    if (abortController) {
      abortController.abort();
      abortController = null;
    }
  };

  const onSuccess = (fn: (data: T) => void) => {
    successCallbacks.push(fn);
  };

  const onError = (fn: (error: Error) => void) => {
    errorCallbacks.push(fn);
  };

  const onFinish = (fn: () => void) => {
    finishCallbacks.push(fn);
  };

  // Execute immediately if requested
  if (options.immediate) {
    execute();
  }

  const returnValue: UseFetchReturn<T, D> = {
    data: data as Ref<T | null>,
    error,
    isFetching,
    isFinished,
    statusCode,
    execute,
    abort,
    onSuccess,
    onError,
    onFinish,
    json: () => {
      options.responseType = 'json';
      return returnValue;
    },
    text: () => {
      options.responseType = 'text';
      return returnValue as UseFetchReturn<string, D>;
    },
  };

  return returnValue;
}

function createHttpClientInstance(): HttpClient {
  const csrfToken = getCsrfToken();

  return {
    get<T = any>(url: MaybeRefOrGetter<string>, options: UseFetchOptions = {}) {
      return createUseFetch<T>(
        (signal) =>
          fetch(toValue(url), {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              Accept: 'application/json',
            },
            signal,
          }),
        options
      );
    },

    post<T = any>(url: MaybeRefOrGetter<string>, data?: any, options: UseFetchOptions = {}) {
      return createUseFetch<T, any>((signal, dataOverride) => {
        const requestData = dataOverride !== undefined ? dataOverride : data;
        return fetch(toValue(url), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
          },
          body: requestData ? JSON.stringify(requestData) : undefined,
          signal,
        });
      }, options);
    },

    postFormData<T = any>(url: MaybeRefOrGetter<string>, formData?: FormData, options: UseFetchOptions = {}) {
      return createUseFetch<T, FormData>((signal, dataOverride) => {
        const requestFormData = dataOverride !== undefined ? dataOverride : formData;
        return fetch(toValue(url), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
          },
          body: requestFormData,
          signal,
        });
      }, options);
    },
  };
}

<script setup lang="ts">
  import { CmsPage } from '../types';
  import { useState } from '../state';
  import { useHttpClient } from '../composables/http';
  import useI18n from '../composables/i18n';

  const { t } = useI18n();
  const { get } = useHttpClient();
  const { channel, locale } = useState();
  const model = defineModel<CmsPage | null>();
  const search = ref('');
  const pages = ref<CmsPage[]>([]);

  const requestUrl = computed(() => {
    const params = new URLSearchParams({
      channel: channel.value,
      locale: locale.value,
    });
    if (search.value) {
      params.append('title', search.value);
    }
    return `${window.editorConfig.routes.getCmsPages}?${params}`;
  });

  const { isFetching, execute, onSuccess, onError } = get(requestUrl);

  onSuccess((data) => {
    pages.value = (data || []).map((page: CmsPage) => {
      const trans = page.translations?.find(t => t.locale === locale.value);
      if (trans) {
        page.url_key = trans.url_key;
        page.page_title = trans.page_title;
      }
      return page;
    });
  });

  onError((error) => {
    console.error('Failed to fetch pages:', error);
  });

  const debouncedFetch = useDebounceFn(() => {
    execute();
  }, 300);

  const onSearch = () => {
    debouncedFetch();
  };

  onMounted(() => execute());

  watch([channel, locale], () => execute());
</script>

<template>
  <div class="flex flex-col overflow-y-hidden">
    <div class="flex items-center mx-2 my-2 px-3 py-1 gap-3 border rounded-lg focus-within:ring focus-within:ring-zinc-700">
      <i-heroicons-magnifying-glass class="w-4 h-4" />
      <input
        v-model="search"
        type="text"
        class="focus:outline-none text-zinc-600"
        :placeholder="t('Search page')"
        @input="onSearch"
      >
    </div>
    <div class="flex-1 overflow-y-auto border-t">
      <div
        v-if="isFetching"
        class="h-20 flex items-center justify-center"
      >
        <Spinner class="h-6 w-6 text-zinc-700" />
      </div>
      <div v-else>
        <a
          v-for="page in pages"
          :key="page.url_key"
          href="#"
          class="flex items-center gap-3 px-3 py-2 outline-none hover:bg-neutral-200 text-sm"
          :class="{ 'bg-neutral-200': model && model.url_key === page.url_key }"
          @click.stop.prevent="model = page"
        >
          <i-mdi-file-document-outline class="w-4 h-4 flex-none text-zinc-700" />
          <span class="truncate flex-1 w-0">
            {{ page.page_title }}
          </span>
        </a>
      </div>
    </div>
  </div>
</template>

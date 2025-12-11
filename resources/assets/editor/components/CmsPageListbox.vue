<script setup lang="ts">
  import { CmsPage } from '../types';
  import { useState } from '../state';
  import { fetchCmsPages } from '../api';
  import useI18n from '../composables/i18n';

  const { t } = useI18n();
  const { channel, locale, getCmsPages } = useState();
  const model = defineModel<CmsPage | null>();
  const search = ref('');

  const pages = computed(() => {
    const allPages = getCmsPages();
    if (!search.value) {
      return allPages;
    }
    const searchLower = search.value.toLowerCase();
    return allPages.filter(page => page.page_title.toLowerCase().includes(searchLower));
  });

  const { isFetching, execute } = fetchCmsPages();

  const debouncedFetch = useDebounceFn(() => {
    execute({
      channel: channel.value,
      locale: locale.value,
      search: search.value
    });
  }, 300);

  const onSearch = () => {
    debouncedFetch();
  };

  onMounted(() => execute({ channel: channel.value, locale: locale.value }));

  watch([channel, locale], () => {
    execute({ channel: channel.value, locale: locale.value });
  });
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

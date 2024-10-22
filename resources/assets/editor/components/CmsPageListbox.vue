<script setup lang="ts">
  import { useStore } from '../store';
  import { CmsPage } from '../types';

  const store = useStore();
  const model = defineModel<CmsPage | null>();
  const search = ref('');
  const { isFetching, data, execute } = store.fetchCmsPages();

  const pages = computed(() => data.value ? data.value.map((page: CmsPage) => {
    const trans = page.translations.find(t => t.locale === store.themeData.locale);
    if (trans) {
      page.url_key = trans.url_key;
      page.page_title = trans.page_title;
    }

    return page;
  }) : []);

  onMounted(() => execute());

  const onSearch = useDebounceFn(() => {
    execute({ query: search.value });
  });

  watch([() => store.themeData.channel, () => store.themeData.locale], () => execute());
</script>

<template>
  <div class="flex flex-col overflow-y-hidden">
    <div class="flex items-center mx-2 my-2 px-3 py-1 gap-3 border rounded-lg focus-within:ring focus-within:ring-gray-700">
      <i-heroicons-magnifying-glass class="w-4 h-4" />
      <input
        v-model="search"
        type="text"
        class="focus:outline-none text-gray-600"
        placeholder="Search page..."
        @input="onSearch"
      >
    </div>
    <div class="flex-1 overflow-y-auto border-t">
      <div
        v-if="isFetching"
        class="h-20 flex items-center justify-center"
      >
        <Spinner class="h-6 w-6 text-gray-700" />
      </div>
      <div v-else>
        <a
          v-for="page in pages"
          :key="page.url_key"
          href="#"
          class="flex items-center gap-3 px-3 py-2 outline-none hover:bg-neutral-200 text-sm"
          :class="{ 'bg-neutral-200': model && model.url_key === page.url_key }"
          @click.stop="model = page"
        >
          <i-mdi-file-document-outline class="w-4 h-4 flex-none text-gray-700" />
          <span class="truncate flex-1 w-0">
            {{ page.id }} {{ page.page_title }}
          </span>
        </a>
      </div>
    </div>
  </div>
</template>

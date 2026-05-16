<script setup lang="ts">
  import { CmsPage } from '../types';
  import { useState } from '../state';
  import { fetchCmsPages } from '../api';
  import useI18n from '../composables/i18n';

  interface Props {
    multiple?: boolean;
  }

  const props = defineProps<Props>();

  const emit = defineEmits<{
    select: [page: CmsPage];
    deselect: [page: CmsPage];
  }>();

  const { t } = useI18n();
  const { channel, locale, getCmsPages } = useState();
  const model = defineModel<CmsPage | CmsPage[] | null>();
  const search = ref('');

  function checkSelected(page: CmsPage): boolean {
    if (props.multiple) {
      return Array.isArray(model.value) && model.value.some(p => p.id === page.id);
    }
    return !Array.isArray(model.value) && !!model.value && model.value.url_key === page.url_key;
  }

  function onItemClick(page: CmsPage) {
    if (props.multiple) {
      const current = Array.isArray(model.value) ? model.value : [];
      if (current.some(p => p.id === page.id)) {
        model.value = current.filter(p => p.id !== page.id);
        emit('deselect', page);
      } else {
        model.value = [...current, page];
        emit('select', page);
      }
      return;
    }
    model.value = page;
    emit('select', page);
  }

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
          :class="{ 'bg-neutral-200': !props.multiple && checkSelected(page), 'bg-neutral-100': props.multiple && checkSelected(page) }"
          @click.stop.prevent="onItemClick(page)"
        >
          <i-mdi-file-document-outline class="w-4 h-4 flex-none text-zinc-700" />
          <span
            class="truncate flex-1 w-0"
            :class="{ 'font-medium text-zinc-900': props.multiple && checkSelected(page) }"
          >
            {{ page.page_title }}
          </span>
          <i-heroicons-check
            v-if="props.multiple && checkSelected(page)"
            class="w-4 h-4 flex-none text-zinc-700"
          />
        </a>
      </div>
    </div>
  </div>
</template>

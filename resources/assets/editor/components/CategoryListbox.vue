<script setup lang="ts">
import { Category } from '../types';
import { useState } from '../state';
import { fetchCategories } from '../api';
import useI18n from '../composables/i18n';

interface Props {
  multiple?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  select: [category: Category];
  deselect: [category: Category];
}>();

const { t } = useI18n();
const { channel, locale, getCategories } = useState();
const model = defineModel<Category | Category[] | null>();
const search = ref('');

function checkSelected(category: Category): boolean {
  if (props.multiple) {
    return Array.isArray(model.value) && model.value.some(c => c.id === category.id);
  }
  return !Array.isArray(model.value) && !!model.value && model.value.id === category.id;
}

function onItemClick(category: Category) {
  if (props.multiple) {
    const current = Array.isArray(model.value) ? model.value : [];
    if (current.some(c => c.id === category.id)) {
      model.value = current.filter(c => c.id !== category.id);
      emit('deselect', category);
    } else {
      model.value = [...current, category];
      emit('select', category);
    }
    return;
  }
  model.value = category;
  emit('select', category);
}

const categories = computed(() => {
  const allCategories = getCategories();
  if (!search.value) {
    return allCategories;
  }
  const searchLower = search.value.toLowerCase();
  return allCategories.filter(category =>
    category.name.toLowerCase().includes(searchLower)
  );
});

const { isFetching, execute } = fetchCategories();

const onSearch = useDebounceFn(() => {
  execute({ search: search.value });
});


onMounted(() => execute());

watch([channel, locale], () => {
  execute();
});
</script>
<template>
  <div class="flex flex-col overflow-y-hidden">
    <div
      v-if="search || categories.length > 2"
      class="flex items-center mx-2 my-2 px-3 py-1 gap-3 border rounded-lg focus-within:ring-2 focus-within:ring-zinc-700"
    >
      <i-heroicons-magnifying-glass class="w-4 h-4" />
      <input
        v-model="search"
        type="text"
        class="focus:outline-none text-zinc-600"
        :placeholder="t('Search category')"
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
          v-for="category in categories"
          :key="category.id"
          href="#"
          class="flex items-center gap-3 px-3 py-2 outline-none hover:bg-neutral-200 text-sm"
          :class="{ 'bg-neutral-200': !props.multiple && checkSelected(category), 'bg-neutral-100': props.multiple && checkSelected(category) }"
          @click.stop.prevent="onItemClick(category)"
        >
          <img
            v-if="category.logo"
            :src="category.logo.small_image_url"
            :alt="category.name"
            class="w-5 h-5 object-cover flex-none"
          >
          <i-bi-tags
            v-else
            class="w-4 h-4 flex-none mr-1 transform rotate-90"
          />
          <span
            class="truncate flex-1 w-0"
            :class="{ 'font-medium text-zinc-900': props.multiple && checkSelected(category) }"
          >
            {{ category.name }}
          </span>
          <i-heroicons-check
            v-if="props.multiple && checkSelected(category)"
            class="w-4 h-4 flex-none text-zinc-700"
          />
        </a>
      </div>
    </div>
  </div>
</template>

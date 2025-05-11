<script setup lang="ts">
  import { useStore } from '../store';
  import { Category } from '../types';

  const store = useStore();
  const model = defineModel<Category | null>();
  const search = ref('');
  const searchInputRef = ref(null)

  defineExpose({ searchInputRef })

  const { isFetching, execute } = store.fetchCategories();

  const categories = computed(() => {
    if (!search.value) {
      return store.categories
    }

    return store.searchCategories(search.value);
  });

  watch([() => store.themeData.channel, () => store.themeData.locale], () => execute());
</script>
<template>
  <div class="flex flex-col overflow-y-hidden">
    <div
      v-if="search || categories.length > 2"
      class="flex items-center mx-2 my-2 px-3 py-1 gap-3 border rounded-lg focus-within:ring focus-within:ring-gray-700"
    >
      <i-heroicons-magnifying-glass class="w-4 h-4" />
      <input
        v-model="search"
        type="text"
        class="focus:outline-none text-gray-600"
        :placeholder="$t('Search category')"
        ref="searchInputRef"
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
          v-for="category in categories"
          :key="category.id"
          href="#"
          class="flex items-center gap-3 px-3 py-2 outline-none hover:bg-neutral-200 text-sm"
          :class="{ 'bg-neutral-200': model && model.id === category.id }"
          @click.stop="model = category"
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
          <span class="truncate flex-1 w-0">
            {{ category.name }}
          </span>
        </a>
      </div>
    </div>
  </div>
</template>
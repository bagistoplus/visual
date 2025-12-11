<script setup lang="ts">
import { Product } from '../types';
import { useState } from '../state';
import { fetchProducts } from '../api';
import useI18n from '../composables/i18n';

const { t } = useI18n();
const { channel, locale, getProducts } = useState();
const model = defineModel<Product | null>();
const search = ref('');

const products = computed(() => getProducts());

const { isFetching, execute } = fetchProducts();

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
      <i-heroicons-magnifying-glass class="w-4 h-4 flex-none" />
      <input
        class="flex-1 w-0 focus:outline-none text-zinc-600 text-sm"
        :placeholder="t('Search product')"
        v-model="search"
        @input="onSearch"
      />
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
          v-for="product in products"
          :key="product.id"
          class="cursor-pointer flex items-center gap-3 px-3 py-2 outline-none hover:bg-neutral-200 text-sm"
          :class="{ 'bg-neutral-200': model && model.id === product.id }"
          @click.stop.prevent="model = product"
        >
          <img
            v-if="product.base_image"
            :src="product.base_image.small_image_url"
            :alt="product.name"
            class="w-5 h-5 object-cover flex-none"
          >
          <i-bi-tag
            v-else
            class="w-4 h-4 flex-none mr-1 transform rotate-90"
          />
          <span class="flex-1 w-0 truncate">{{ product.name }}</span>
        </a>
      </div>
    </div>
  </div>
</template>
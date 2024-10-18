<script setup lang="ts">
  import { useStore } from '../store';
  import { Product } from '../types';

  const store = useStore();
  const model = defineModel();
  const search = ref('');
  const { isFetching, data, execute } = store.fetchProducts();

  const products = computed<Product[]>(() => {
    return data.value ? data.value.data : [];
  });

  onMounted(() => execute());

  const onSearch = useDebounceFn(() => {
    execute({ query: search.value });
  });

  watch([() => store.themeData.channel, () => store.themeData.locale], () => execute());
</script>

<template>
  <div class="flex flex-col overflow-y-hidden">
    <div class="flex items-center mx-2 my-2 px-3 py-1 gap-3 border rounded-lg focus-within:ring focus-within:ring-gray-700">
      <i-heroicons-magnifying-glass class="w-4 h-4 flex-none" />
      <input
        class="flex-1 w-0 focus:outline-none text-gray-600 text-sm"
        placeholder="Search product"
        v-model="search"
        @input="onSearch"
      />
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
          v-for="product in products"
          :key="product.id"
          class="cursor-pointer flex items-center gap-3 px-3 py-2 outline-none hover:bg-neutral-200 text-sm"
          :class="{ 'bg-neutral-200': model === product.id }"
          @click.stop="model = product.id"
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

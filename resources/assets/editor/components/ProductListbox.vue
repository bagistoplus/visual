<script setup lang="ts">
  import { Product } from '../types';
  import Spinner from './Spinner.vue'
  const props = defineProps<{ isLoading: boolean, products: Product[] }>();
  const model = defineModel();
  const search = defineModel<string>('search');
</script>

<template>
  <div class="flex flex-col overflow-y-hidden">
    <div class="flex items-center mx-2 my-2 px-3 py-1 gap-3 border rounded-lg focus-within:ring focus-within:ring-gray-700">
      <i-heroicons-magnifying-glass class="w-4 h-4 flex-none" />
      <input
        class="flex-1 w-0 focus:outline-none text-gray-600 text-sm"
        placeholder="Search product"
        v-model="search"
      />
    </div>
    <div class="flex-1 overflow-y-auto border-t">
      <div
        v-if="isLoading"
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

<script setup lang="ts">
  import { Category } from '../types';

  const model = defineModel();
  const search = defineModel<string>('search');
  const props = defineProps<{ categories: Category[] }>();
</script>
<template>
  <div class="flex flex-col overflow-y-hidden">
    <div v-if="search || categories.length > 2" class="flex items-center mx-2 my-2 px-3 py-1 gap-3 border rounded-lg focus-within:ring focus-within:ring-gray-700">
      <MagnifyingGlassIcon class="w-4 h-4" />
      <input v-model="search" type="text" class="focus:outline-none text-gray-600" placeholder="Search category">
    </div>
    <ul class="flex-1 overflow-y-auto border-t">
      <li v-for="category in categories" :key="category.id">
        <a href="#" class="flex items-center gap-3 px-3 py-2 outline-none hover:bg-neutral-200 text-sm" :class="{ 'bg-neutral-200': model === category.id }" @click="model = category.id">
          <img v-if="category.logo" :src="category.logo.small_image_url" :alt="category.name" class="w-5 h-5 object-cover flex-none">
          <i-bi-tags v-else class="w-4 h-4 flex-none mr-1 transform rotate-90" />
          <span class="truncate flex-1 w-0">
            {{ category.name }}
          </span>
        </a>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { Product } from '../types';
import { useState } from '../state';
import { fetchProducts } from '../api';
import useI18n from '../composables/i18n';

interface Props {
  multiple?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  select: [product: Product];
  deselect: [product: Product];
}>();

const { t } = useI18n();
const { channel, locale, getProducts } = useState();
const model = defineModel<Product | Product[] | null>();
const search = ref('');

function checkSelected(product: Product): boolean {
  if (props.multiple) {
    return Array.isArray(model.value) && model.value.some(p => p.id === product.id);
  }
  return !Array.isArray(model.value) && !!model.value && model.value.id === product.id;
}

function onItemClick(product: Product) {
  if (props.multiple) {
    const current = Array.isArray(model.value) ? model.value : [];
    if (current.some(p => p.id === product.id)) {
      model.value = current.filter(p => p.id !== product.id);
      emit('deselect', product);
    } else {
      model.value = [...current, product];
      emit('select', product);
    }
    return;
  }
  model.value = product;
  emit('select', product);
}

const products = computed(() => {
  const allProducts = getProducts();
  if (!search.value) {
    return allProducts;
  }
  const searchLower = search.value.toLowerCase();
  return allProducts.filter(product =>
    product.name.toLowerCase().includes(searchLower)
  );
});

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
        v-model="search"
        class="flex-1 w-0 focus:outline-none text-zinc-600 text-sm"
        :placeholder="t('Search product')"
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
          v-for="product in products"
          :key="product.id"
          class="cursor-pointer flex items-center gap-3 px-3 py-2 outline-none hover:bg-neutral-200 text-sm"
          :class="{ 'bg-neutral-200': !props.multiple && checkSelected(product), 'bg-neutral-100': props.multiple && checkSelected(product) }"
          @click.stop.prevent="onItemClick(product)"
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
          <span
            class="flex-1 w-0 truncate"
            :class="{ 'font-medium text-zinc-900': props.multiple && checkSelected(product) }"
          >{{ product.name }}</span>
          <i-heroicons-check
            v-if="props.multiple && checkSelected(product)"
            class="w-4 h-4 flex-none text-zinc-700"
          />
        </a>
      </div>
    </div>
  </div>
</template>

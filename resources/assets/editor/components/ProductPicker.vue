<script setup lang="ts">
  import { Popover } from '@ark-ui/vue/popover';
  import ProductListbox from './ProductListbox.vue'
  import { useStore } from '../store';
  import { Product } from '../types';

  const store = useStore();
  const model = defineModel<number | null>();
  const opened = ref(false);

  const selectedProduct = computed<Product | null>({
    get: () => model.value ? store.getProduct(model.value) : null,
    set: (product: Product | null) => {
      model.value = product ? product.id : null;
    }
  });

</script>

<template>
  <div>
    <Popover.Root v-model:open="opened">
      <Popover.Trigger as-child>
        <div
          class="flex items-center w-full gap-3 cursor-pointer border rounded px-3 h-10 text-sm"
          role="button"
        >
          <template v-if="selectedProduct">
            <img
              v-if="selectedProduct.base_image"
              :src="selectedProduct.base_image.small_image_url"
              :alt="selectedProduct.name"
              class="w-5 h-5 object-cover flex-none"
            >
            <i-bi-tags
              v-else
              class="w-4 h-4 flex-none transform rotate-90"
            />
            <span class="flex-1 w-0 truncate">{{ selectedProduct.name }}</span>
            <button
              class="flex-none rounded-lg hover:bg-neutral-200 p-1"
              @click.stop="model = null"
            >
              <i-heroicons-x-mark />
            </button>
          </template>
          <span v-else>{{ $t('Select product') }}</span>
        </div>
      </Popover.Trigger>
      <Popover.Positioner class="w-[var(--reference-width)] !z-10">
        <Popover.Content class="border bg-white shadow rounded-lg outline-none max-h-80 flex flex-col">
          <Popover.Arrow
            style="--arrow-size: 0.5rem;"
            class="bg-white"
          >
            <Popover.ArrowTip class="border-t border-l" />
          </Popover.Arrow>

          <ProductListbox
            v-model="selectedProduct"
            @update:model-value="(opened = false)"
          />
        </Popover.Content>
      </Popover.Positioner>
    </Popover.Root>
  </div>
</template>
<script setup lang="ts">
  import { Popover } from '@ark-ui/vue/popover';
  import { useStore } from '../store';
  import { Category } from '../types';

  const store = useStore();
  const model = defineModel<number | null>();
  const props = defineProps<{ label: string; }>();
  const opened = ref(false);

  const selectedCategory = computed<Category | null>({
    get: () => model.value ? store.categories.find(c => c.id === model.value) : null,
    set: (category: Category | null) => {
      model.value = category ? category.id : null;
    }
  });
</script>

<template>
  <Popover.Root v-model:open="opened">
    <label
      class="text-sm font-medium block mb-2"
      v-if="label"
    >
      {{ label }}
    </label>
    <Popover.Trigger as-child>
      <div class="flex items-center w-full gap-3 cursor-pointer border rounded px-3 h-10 text-sm">
        <template v-if="selectedCategory">
          <img
            v-if="selectedCategory.logo"
            :src="selectedCategory.logo.small_image_url"
            :alt="selectedCategory.name"
            class="w-5 h-5 object-cover flex-none"
          >
          <i-bi-tags
            v-else
            class="w-4 h-4 flex-none transform rotate-90"
          />
          <span class="flex-1 w-0 truncate">{{ selectedCategory.name }}</span>
          <button
            class="flex-none rounded-lg hover:bg-neutral-200 p-1"
            @click.stop="model = null"
          >
            <i-heroicons-x-mark />
          </button>
        </template>
        <span v-else>Select category</span>
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

        <CategoryListbox
          v-model="selectedCategory"
          @update:model-value="opened = false"
        />
      </Popover.Content>
    </Popover.Positioner>
  </Popover.Root>
</template>

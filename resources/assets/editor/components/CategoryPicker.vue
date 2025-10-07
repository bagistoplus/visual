<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Popover } from '@ark-ui/vue/popover';
import { Category } from '../types';
import { useHttpClient } from '../composables/http';
import useI18n from '../composables/i18n';

interface Props {
  field: PropertyField;
}

defineProps<Props>();

const { t } = useI18n();
const { get } = useHttpClient();
const model = defineModel<number | null>();
const opened = ref(false);

const categoriesCache = ref<Map<number, Category>>(new Map());
const selectedCategory = ref<Category | null>(null);

// Fetch category details when model changes
watch(() => model.value, async (categoryId) => {
  if (!categoryId) {
    selectedCategory.value = null;
    return;
  }

  if (categoriesCache.value.has(categoryId)) {
    selectedCategory.value = categoriesCache.value.get(categoryId)!;
    return;
  }

  const request = get<Category>(`/api/categories/${categoryId}`);

  request.onSuccess((data) => {
    categoriesCache.value.set(categoryId, data);
    selectedCategory.value = data;
  });

  request.onError((error) => {
    console.error('Failed to fetch category:', error);
  });

  await request.execute();
}, { immediate: true });

const updateCategory = (category: Category | null) => {
  selectedCategory.value = category;
  model.value = category ? category.id : null;
  if (category) {
    categoriesCache.value.set(category.id, category);
  }
};
</script>

<template>
  <div>
    <label
      v-if="field.label"
      class="text-sm block mb-1 font-medium text-gray-700"
    >
      {{ field.label }}
    </label>
    <Popover.Root v-model:open="opened">
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
          <span v-else>{{ t('Select category') }}</span>
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
            @update:model-value="updateCategory($event); opened = false"
          />
        </Popover.Content>
      </Popover.Positioner>
    </Popover.Root>
  </div>
</template>

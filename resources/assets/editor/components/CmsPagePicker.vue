<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Popover } from '@ark-ui/vue/popover';
import { CmsPage } from '../types';
import { useState } from '../state';
import useI18n from '../composables/i18n';

interface Props {
  field: PropertyField;
}

defineProps<Props>();

const { t } = useI18n();
const { getCmsPage } = useState();
const model = defineModel<number | null>();
const opened = ref(false);

const selectedPage = computed({
  get: () => {
    if (!model.value) {
      return null;
    }
    return getCmsPage(model.value);
  },
  set: (page: CmsPage | null) => {
    model.value = page ? page.id : null;
  }
});
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
        <div
          role="button"
          class="flex items-center w-full gap-3 cursor-pointer border rounded px-3 h-10 text-sm"
        >
          <template v-if="selectedPage">
            <i-mdi-file-document-outline class="w-4 h-4 flex-none" />
            <span class="flex-1 w-0 truncate">{{ selectedPage.page_title }}</span>
            <button
              class="flex-none rounded-lg hover:bg-neutral-200 p-1"
              @click.stop="model = null"
            >
              <i-heroicons-x-mark />
            </button>
          </template>
          <span v-else>{{ t('Select page') }}</span>
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

          <CmsPageListbox
            v-model="selectedPage"
            @update:model-value="opened = false"
          />
        </Popover.Content>
      </Popover.Positioner>
    </Popover.Root>
  </div>
</template>

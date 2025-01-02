<script setup lang="ts">
  import { Popover } from '@ark-ui/vue/popover';
  import { CmsPage } from '../types';
  import { useStore } from '../store';

  const store = useStore();
  const model = defineModel<number | null>();
  const props = defineProps<{ label: string; }>();
  const opened = ref(false);

  const selectedPage = computed<CmsPage | null>({
    get: () => (model.value ? store.getCmsPage(model.value) : null),
    set: (page: CmsPage | null) => {
      model.value = page ? page.id : null;
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
      <div
        role="button"
        class="flex items-center w-full gap-3 cursor-pointer border rounded px-3 h-10 text-sm"
      >
        <template v-if="selectedPage">
          <i-mdi-file-document-outline class="w-4 h-4 flex-none" />
          <span class="flex-1 w-0 truncate">{{ selectedPage.page_title }}</span>
          <button
            class="flex-none rounded-lg hover:bg-neutral-200 p-1"
            @click.stop="selectedPage = null"
          >
            <i-heroicons-x-mark />
          </button>
        </template>
        <span v-else>{{ $t('Select page') }}</span>
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
</template>

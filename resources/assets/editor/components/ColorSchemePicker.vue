<script setup lang="ts">
  import { Popover } from '@ark-ui/vue/popover';
  import { useStore } from '../store';

  const store = useStore();
  const isSchemesDefined = computed(() => {
    return Object.keys(store.colorSchemes!).length > 0;
  });

  const model = defineModel<string | null>();
  const opened = ref(false);

  const selectedScheme = computed(() => {
    return model.value && store.colorSchemes
      ? (store.colorSchemes as Record<string, any>)[model.value]
      : null;
  });

  function onSelectScheme(id: string) {
    model.value = id;
    opened.value = false;
  }
</script>

<template>
  <div v-if="isSchemesDefined">
    <Popover.Root v-model:open="opened">
      <Popover.Trigger class="border rounded w-full flex justify-between h-10 px-3 items-center">
        <div
          v-if="selectedScheme"
          class="flex flex-1 gap-3"
        >
          <div
            class="w-6 rounded flex-none"
            :style="{ backgroundColor: selectedScheme.background, color: selectedScheme['on-background'] }"
          >A</div>
          <div class="capitalize flex-1 text-left">{{ model }}</div>
          <button
            class="ml-auto flex-none mr-1 p-1 rounded-lg hover:bg-zinc-100"
            @click.stop="model = null"
          >
            <i-heroicons-x-mark class="w-4 h-4" />
          </button>
        </div>
        <span v-else>Select a color scheme</span>
        <i-heroicons-chevron-up-down class="flex-none h-4 w-4" />
      </Popover.Trigger>

      <Teleport to="body">
        <Popover.Positioner>
          <Popover.Content class="border  rounded shadow-md w-[var(--reference-width)] z-50 bg-white -mt-1 overflow-y-auto max-h-[360px]">
            <div v-for="(scheme, id) in store.colorSchemes">
              <div
                class=" px-3 py-3 hover:bg-gray-100 relative"
                :class="{ 'bg-gray-100': model === String(id) }"
              >
                <i-heroicons-check-circle-solid
                  v-if="model === String(id)"
                  class="w-5 h-5 absolute top-2 right-2 text-green-400"
                />
                <ColorSchemePreview
                  :scheme="scheme"
                  :id="String(id)"
                  @click="onSelectScheme(String(id))"
                />
              </div>
            </div>
          </Popover.Content>
        </Popover.Positioner>
      </Teleport>
    </Popover.Root>
  </div>

  <div
    v-else
    class="text-sm text-red-400"
  >
    Oops! This theme doesn’t have any color schemes to show.
  </div>
</template>

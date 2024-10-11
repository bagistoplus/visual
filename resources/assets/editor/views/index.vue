<script lang="ts" setup>
import { Popover } from '@ark-ui/vue/popover'
import { useStore } from '../store';

const store = useStore();
const popoverOpen = ref(false);

function togglePopover(event: unknown) {
  popoverOpen.value = !popoverOpen.value;
}

function onContentSectionsReorder(order: string[]) {
  store.setContentSectionsOrder(order);
}

function onActivateSection(sectionId: string) {
  store.activateSection(sectionId);
}

function onDeactivateSection(sectionId: string) {
  store.deactivateSection(sectionId);
}
</script>

<template>
  <div class="w-full h-full flex flex-col">
    <header class="flex none px-4 py-3 border-b font-medium">
      <h1>Page title</h1>
    </header>
    <div class="flex-1 overflow-y-auto p-3">
      <SectionsGroup
        static
        title="Layout Sections g"
        :sections="store.beforeContentSections"
        @addSection="togglePopover"
        @activateSection="onActivateSection"
        @deactivateSection="onDeactivateSection"
      />
      <hr class="my-2">

      <SectionsGroup
        title="Template Sections"
        :order="store.contentSectionsOrder"
        :sections="store.contentSections"
        @reorder="onContentSectionsReorder"
        @addSection="togglePopover"
        @activateSection="onActivateSection"
        @deactivateSection="onDeactivateSection"
      />
      <hr class="my-2">
      <SectionsGroup static title="Layout Sections" :sections="[]" @addSection="togglePopover"/>
    </div>

    <Popover.Root v-model:open="popoverOpen" :positioning="{
      placement: 'left-start',
      gutter: 16,
      offset: { mainAxis: 120, crossAxis: 120 },
    }">
      <Popover.Positioner>
        <Popover.Content class="bg-white w-48 border rounded shadow p-1">
          <header>
            <TextInput />
        </header>
        </Popover.Content>
      </Popover.Positioner>
    </Popover.Root>
  </div>
</template>

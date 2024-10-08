<script lang="ts" setup>
import { useStore } from '../store';

const store = useStore();
const popover = useTemplateRef('popover');

function togglePopover(event: unknown) {
  popover.value.toggle(event);
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

    <Popover ref="popover" class="ml-64 -mt-8">
      <div class="-m-3 w-72 h-[480px]">
        <header>
          <IconField>
            <InputIcon class="!-mt-3.5">
              <MagnifyingGlassIcon class="inline w-4 h-4"/>
            </InputIcon>
            <InputText placeholder="Search" class="w-full" />
          </IconField>
        </header>
      </div>
    </Popover>
  </div>
</template>

<script setup lang="ts">
import { Menu } from '@ark-ui/vue/menu'
import { SelectionDetails } from 'node_modules/@ark-ui/vue/dist/components/menu/menu';

import { useStore } from '../store';
import { groupSettings } from '../utils';
import { Block } from '../types';

const router = useRouter();
const route = useRoute<'/[section]'>();
const store = useStore();

const sectionData = computed(() => store.getSectionData(route.params.section));
const section = computed(() => sectionData.value ? store.getSectionBySlug(sectionData.value.type) : null);
const isRemovable = computed(() => store.canRemoveSection(route.params.section));

const groupedSettings = computed(() => groupSettings(section.value?.settings || []));

const blocksData = computed(() => sectionData.value.blocksOrder.map(id => sectionData.value.blocks[id]));
const availableBlocks = computed(() => {
  const sectionBlocks = section.value?.blocks || [];

  if (blocksData.value.length === 0) {
    return sectionBlocks;
  }

  return sectionBlocks.filter(block => {
    const matchingBlocks = blocksData.value.filter(b => b.type === block.type);

    if (block.limit === undefined) {
      return true;
    }

    return matchingBlocks.length < block.limit;
  });
});

function goBack() {
  router.back();
}

function onRemoveSection() {
  store.removeSection(route.params.section);
  goBack();
}

function getSettingValue(id: string): any {
  return store.getThemeDataValue(`sectionsData.${sectionData.value.id}.settings.${id}`);
}

function setSettingValue(id: string, value: any): any {
  return store.updateThemeDataValue(
    `sectionsData.${sectionData.value.id}.settings.${id}`,
    value
  );
}

function addBlock(type: string) {
  const block = section.value!.blocks.find(b => b.type === type) as Block
  store.addBlockToSection(sectionData.value.id, block);
}

function onBlocksReorder(order: string[]) {
  store.updateThemeDataValue(
    `sectionsData.${sectionData.value.id}.blocksOrder`,
    order
  )
}
</script>

<template>
  <div v-if="section" class="flex flex-col h-full overflow-hidden relative w-full">
    <header class="flex-none p-2 border-b">
      <div class="flex items-center">
        <button class="p-1 px-2 rounded hover:bg-gray-200 focus:outline-none" @click="goBack">
          <ArrowLeftIcon class="inline w-4" />
        </button>
        <span class="ml-2">{{ sectionData.settings.heading || section.name }}</span>
      </div>
      <p v-if="section.description" class="mt-2 text-sm">
        {{ section.description }}
      </p>
    </header>

    <section class="flex-1 overflow-y-auto">
      <ArkAccordionRoot multiple :defaultValue="['blocks', 'Settings']">
        <ArkAccordionItem v-if="section.blocks.length > 0" value="blocks">
          <ArkAccordionItemTrigger>
            Blocks
            <ArkAccordionItemIndicator><ChevronDownIcon /></ArkAccordionItemIndicator>
          </ArkAccordionItemTrigger>
          <ArkAccordionItemContent>
            <BlocksGroup
              class="-mt-2 mb-2"
              :blocks="blocksData"
              :order="sectionData.blocksOrder"
              @reorder="onBlocksReorder"
            />

            <Menu.Root v-if="availableBlocks.length > 0" @select="(details: SelectionDetails) => addBlock(details.value)">
              <Menu.Trigger class="w-full text-sm rounded-lg cursor-pointer outline-0 inline-flex items-center gap-2 px-2 py-1 hover:bg-gray-200 focus:ring-1 focus:ring-gray-700">
                <PlusCircleIcon class="w-4 h-4 inline mr-1"/>
                Add Block
              </Menu.Trigger>
              <Menu.Positioner class="w-[var(--reference-width)] !z-10">
                <Menu.Content class="flex flex-col outline-0 gap-1 p-1 bg-white shadow border rounded-lg data-[state=open]:animate-fade-in">
                  <Menu.Item v-for="block in availableBlocks" :key="block.type" :value="block.type" class="outline-0 rounded cursor-pointer px-2 py-2 hover:bg-gray-200">
                    {{ block.name  }}
                  </Menu.Item>
                </Menu.Content>
              </Menu.Positioner>
            </Menu.Root>
          </ArkAccordionItemContent>
        </ArkAccordionItem>

        <ArkAccordionItem v-for="settingGroup in groupedSettings" :value="settingGroup.name" :key="settingGroup.name">
          <ArkAccordionItemTrigger>
            {{  settingGroup.name }}
            <ArkAccordionItemIndicator><ChevronDownIcon /></ArkAccordionItemIndicator>
          </ArkAccordionItemTrigger>
          <ArkAccordionItemContent>
            <Setting
              v-for="setting in settingGroup.settings"
              :key="setting.id"
              :value="getSettingValue(setting.id)"
              :setting="setting"
              @input="(val: any) => setSettingValue(setting.id, val)"
            />
          </ArkAccordionItemContent>
        </ArkAccordionItem>
      </ArkAccordionRoot>

      <template v-if="section.settings.length > 0"></template>
      <p v-else>{{ "This section has no configuration" }}</p>
    </section>

    <footer v-if="isRemovable" class="flex-none border-t">
      <button class="flex items-center w-full text-left py-2 px-4 text-red-500 hover:bg-gray-100" @click="onRemoveSection">
        <TrashIcon class="inline mr-2 w-4" />
        Remove section
      </button>
    </footer>
  </div>
</template>

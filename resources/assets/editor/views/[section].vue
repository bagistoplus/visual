<script setup lang="ts">
import { useStore } from '../store';
import { groupSettings } from '../utils';

const router = useRouter();
const route = useRoute<'/[section]'>();
const store = useStore();

const sectionData = computed(() => store.getSectionData(route.params.section));
const section = computed(() => sectionData.value ? store.getSectionBySlug(sectionData.value.type) : null);
const isRemovable = computed(() => store.canRemoveSection(route.params.section));

const groupedSettings = computed(() => groupSettings(section.value?.settings || []));

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
            Blocks
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

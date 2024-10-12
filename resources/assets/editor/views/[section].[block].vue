<script setup lang="ts">
import { Menu } from '@ark-ui/vue/menu'
import { useStore } from '../store';
import { groupSettings } from '../utils';

const router = useRouter();
const route = useRoute('/[section].[block]');
const store = useStore();

const blockData = computed(() => store.getSectionBlockData(route.params.section, route.params.block));
const block = computed(() => blockData.value ? store.getSectionBlockByType(route.params.section, blockData.value.type) : null);
const groupedSettings = computed(() => groupSettings(block.value?.settings || []));

function goBack() {
  router.back();
}

function getSettingValue(id: string): any {
  return store.getThemeDataValue([
    'sectionsData',
    route.params.section,
    'blocks',
    route.params.block,
    'settings',
    id
  ]);
}

function setSettingValue(id: string, value: any): any {
  return store.updateThemeDataValue(
    [
      'sectionsData',
      route.params.section,
      'blocks',
      route.params.block,
      'settings',
      id
    ],
    value
  );
}

function removeBlock() {}
</script>

<template>
  <div v-if="block" class="flex flex-col h-full overflow-hidden relative w-full">
    <header class="flex-none p-2 border-b">
      <div class="flex items-center">
        <button class="p-1 px-2 rounded hover:bg-gray-200 focus:outline-none" @click="goBack">
          <ArrowLeftIcon class="inline w-4" />
        </button>
        <span class="ml-2 truncate">{{ blockData.settings.title|| blockData.settings.heading || block.name }}</span>
      </div>
      <p v-if="block.description" class="mt-2 text-sm">
        {{ block.description }}
      </p>
    </header>

    <section class="flex-1 overflow-y-auto">
      <ArkAccordionRoot multiple :defaultValue="['blocks', 'Settings']">
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

      <template v-if="block.settings.length > 0"></template>
      <p v-else>{{ "This section has no configuration" }}</p>
    </section>

    <footer class="flex-none border-t">
      <button class="flex items-center w-full text-left py-2 px-4 text-red-500 hover:bg-gray-100" @click="onRemoveBlock">
        <TrashIcon class="inline mr-2 w-4" />
        Remove block
      </button>
    </footer>
  </div>
</template>

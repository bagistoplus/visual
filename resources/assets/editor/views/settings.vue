<script setup lang="ts">
  import { Accordion } from '@ark-ui/vue/accordion';
  import { useStore } from '../store';

  const router = useRouter();
  const store = useStore();
  function goBack() {
    router.back();
  }

  function getSettingValue(id: string): any {
    return store.getThemeDataValue(['settings', id]);
  }

  function setSettingValue(id: string, value: any): any {
    return store.updateThemeDataValue(
      ['settings', id],
      value
    );
  }
</script>
<template>
  <div class="flex flex-col h-full overflow-hidden relative w-full">
    <header class="flex-none p-2 border-b">
      <div class="flex items-center">
        <button
          class="p-1 px-2 rounded hover:bg-gray-200 focus:outline-none"
          @click="goBack"
        >
          <i-heroicons-arrow-left class="inline w-4" />
        </button>
        <span class="ml-2 truncate">{{ $t('Theme settings') }}</span>
      </div>
    </header>
    <section class="flex-1 overflow-y-auto">
      <Accordion.Root collapsible>
        <Accordion.Item
          v-for="settingGroup in store.settingsSchema"
          :value="settingGroup.name"
          :key="settingGroup.name"
        >
          <Accordion.ItemTrigger>
            {{ settingGroup.name }}
            <Accordion.ItemIndicator>
              <i-heroicons-chevron-down />
            </Accordion.ItemIndicator>
          </Accordion.ItemTrigger>
          <Accordion.ItemContent>
            <Setting
              v-for="setting in settingGroup.settings"
              :key="setting.id"
              :value="getSettingValue(setting.id)"
              :setting="setting"
              @input="(val: any) => setSettingValue(setting.id, val)"
            />
          </Accordion.ItemContent>
        </Accordion.Item>
      </Accordion.Root>
    </section>
  </div>
</template>
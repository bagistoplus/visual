<script setup lang="ts">
  import { Menu } from '@ark-ui/vue/menu';
  import { useStore } from '../store';

  const store = useStore();
  const router = useRouter();

  const emit = defineEmits<{
    (e: 'select', value: any): void;
  }>();

  const model = defineModel<string>();
  const selected = computed(() => store.templates.find(t => t.template === model.value));


  watch(
    [store.templates, () => store.themeData.template],
    ([templates, activeTemplate]) => {
      if (activeTemplate && model.value !== activeTemplate) {
        model.value = activeTemplate;
      } else if (!model.value && templates.length) {
        model.value = templates[0].template;
      }
    },
    { immediate: true }
  );

  function onSelect({ value }: { value: string }) {
    model.value = value;
    emit('select', selected.value);
  }
</script>

<template>
  <Menu.Root @select="onSelect">
    <Menu.Trigger class="min-w-44 px-4 py-2 appearance-none rounded-lg cursor-pointer inline-flex gap-3 outline-none relative select-none items-center justify-center hover:bg-gray-200">
      <template v-if="selected">
        <span v-html="selected.icon"></span>
        {{ selected.label }}
      </template>
      <Menu.Indicator>
        <i-heroicons-chevron-down class="inline w-4" />
      </Menu.Indicator>
    </Menu.Trigger>
    <Menu.Positioner class="w-64">
      <Menu.Content class="pointer-events-none border shadow flex gap-1 p-1 flex-col outline-none rounded bg-white data-[state=open]:animate-fade-in">
        <template
          v-for="t in store.templates"
          :key="t.template"
        >
          <Menu.Separator v-if="t.template === '__separator__'" />
          <Menu.Item
            v-else
            :value="t.template"
            class="rounded cursor-pointer flex items-center h-9 px-3 gap-3 hover:bg-gray-200"
          >
            <span v-html="t.icon"></span>
            {{ t.label }}
          </Menu.Item>
        </template>
      </Menu.Content>
    </Menu.Positioner>
  </Menu.Root>
</template>
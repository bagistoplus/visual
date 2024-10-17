<script setup lang="ts">
  import { Menu } from '@ark-ui/vue/menu';

  interface Props {
    templates?: { icon: string; label: string; url: string; template: string }[];
  }
  const props = withDefaults(defineProps<Props>(), {
    templates: () => [
      { icon: '', label: 'Home page', url: '/', template: 'index' },
      { icon: '', label: 'Categories', url: '/', template: 'categories' },
      { icon: '', label: 'Cart', url: '/', template: 'cart' },
    ]
  });

  const emit = defineEmits<{
    (e: 'select', value: any): void;
  }>();

  const model = defineModel<string>();
  const selected = computed(() => props.templates.find(t => t.template === model.value));

  onBeforeMount(() => {
    if (!model.value) {
      model.value = props.templates[0].template;
    }
  });
  function onSelect({ value }: { value: string }) {
    model.value = value;
    emit('select', selected.value);
  }
</script>

<template>
  <Menu.Root @select="onSelect">
    <Menu.Trigger class="min-w-44 py-2 appearance-none rounded-lg cursor-pointer inline-flex gap-3 outline-none relative select-none items-center justify-center hover:bg-gray-200">
      <i-heroicons-home class="inline w-4" />
      {{ selected!.label }}
      <Menu.Indicator>
        <i-heroicons-chevron-down class="inline w-4" />
      </Menu.Indicator>
    </Menu.Trigger>
    <Menu.Positioner class="w-56">
      <Menu.Content class="pointer-events-none border shadow flex gap-1 p-1 flex-col outline-none rounded bg-white data-[state=open]:animate-fade-in">
        <Menu.Item
          v-for="t in templates"
          :key="t.template"
          :value="t.template"
          class="rounded cursor-pointer flex items-center h-9 px-3 gap-3 hover:bg-gray-200"
        >
          <i-heroicons-home class="inline w-4" />
          {{ t.label }}
        </Menu.Item>
      </Menu.Content>
    </Menu.Positioner>
  </Menu.Root>
</template>

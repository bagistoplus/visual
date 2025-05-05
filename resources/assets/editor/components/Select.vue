<script setup lang="ts">
  import { createListCollection, Select } from '@ark-ui/vue/select'

  interface Props {
    label?: string;
    options: {
      label: string;
      value: any;
    }[];
  };

  const props = defineProps<Props>();
  const value = defineModel<any>()
  const collection = computed(() => createListCollection({ items: props.options }))
</script>

<template>
  <Select.Root
    :collection="collection"
    class="gap-2 flex flex-col"
    :model-value="[value!]"
    @update:model-value="v => value = v[0]"
  >
    <Select.Label
      v-if="label"
      class="text-sm font-medium"
    >{{ label }}</Select.Label>

    <Select.Control>
      <Select.Trigger
        class="text-gray-500 border px-3 h-10 gap-2 font-medium w-full cursor-pointer rounded inline-flex outline-0 items-center appearance-none justify-between focus:shadow focus:ring focus:ring-gray-700"
      >
        <Select.ValueText />
        <Select.Indicator>
          <i-heroicons-chevron-down class="w-4 h-4" />
        </Select.Indicator>
      </Select.Trigger>
    </Select.Control>
    <Teleport to="body">
      <Select.Positioner class="w-[var(--reference-width)]">
        <Select.Content class="bg-white rounded shadow gap-1 p-1 flex flex-col  border data-[state=open]:animate-fade-in">
          <Select.Item
            v-for="item in collection.items"
            :key="item.value"
            :item="item.value"
            class="rounded cursor-pointer flex items-center justify-between px-2 h-10 data-[highlighted]:bg-gray-100"
          >
            <Select.ItemText>{{ item.label }}</Select.ItemText>
            <Select.ItemIndicator>✓</Select.ItemIndicator>
          </Select.Item>
        </Select.Content>
      </Select.Positioner>
    </Teleport>
    <Select.HiddenSelect />
  </Select.Root>
</template>
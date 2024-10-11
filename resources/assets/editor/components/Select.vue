<script setup lang="ts">
import { createListCollection } from '@ark-ui/vue/select'

interface Props {
  label: string;
  options: {
    label: string;
    value: string;
  }[];
};

const props = defineProps<Props>();
const value = defineModel<string>()
const collection = computed(() => createListCollection({items: props.options}))
</script>
<template>
  <ArkSelectRoot
    :collection="collection"
    class="gap-2 flex flex-col"
    :model-value="[value!]"
    @update:model-value="v => value = v[0]">
    <ArkSelectLabel class="text-sm font-medium">{{ label }}</ArkSelectLabel>
    <ArkSelectControl>
      <ArkSelectTrigger class="text-gray-500 border px-3 h-10 gap-2 font-medium w-full cursor-pointer rounded inline-flex outline-0 items-center appearance-none justify-between focus:shadow focus:ring focus:ring-gray-700">
        <ArkSelectValueText />
        <ArkSelectIndicator>
          <ChevronDownIcon class="w-4 h-4" />
        </ArkSelectIndicator>
      </ArkSelectTrigger>
    </ArkSelectControl>
    <Teleport to="body">
      <ArkSelectPositioner class="w-[var(--reference-width)]">
        <ArkSelectContent class="bg-white rounded shadow gap-1 p-1 flex flex-col  border data-[state=open]:animate-fade-in">
          <ArkSelectItem
            v-for="item in collection.items"
            :key="item.value"
            :item="item.value"
            class="rounded cursor-pointer flex items-center justify-between px-2 h-10 data-[highlighted]:bg-gray-100">
            <ArkSelectItemText>{{ item.label }}</ArkSelectItemText>
            <ArkSelectItemIndicator>✓</ArkSelectItemIndicator>
          </ArkSelectItem>
        </ArkSelectContent>
      </ArkSelectPositioner>
    </Teleport>
    <ArkSelectHiddenSelect />
  </ArkSelectRoot>
</template>

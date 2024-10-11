<script setup lang="ts">
interface Props {
  label: string;
  options: {
    value: any;
    label: string
  }[];
};

const value = defineModel<string>();
const props = defineProps<Props>();
</script>
<template>
  <ArkRadioGroupRoot v-model="value" class="relative gap-4 flex flex-col">
    <ArkRadioGroupLabel class="text-sm font-medium">
      {{ label }}
    </ArkRadioGroupLabel>
    <ArkRadioGroupIndicator />
    <ArkRadioGroupItem
      v-for="option in options"
      :key="option.label"
      :value="option.value"
      class="flex items-center gap-3 cursor-pointer">
      <ArkRadioGroupItemControl class="w-5 h-5 rounded-full border data-[state=checked]:outline data-[state=checked]:outline-4 data-[state=checked]:-outline-offset-8 data-[state=checked]:border-gray-700" />
      <ArkRadioGroupItemText class="text-md font-medium">{{ option.label }}</ArkRadioGroupItemText>
      <ArkRadioGroupItemHiddenInput />
    </ArkRadioGroupItem>
  </ArkRadioGroupRoot>
</template>

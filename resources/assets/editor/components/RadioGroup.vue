<script setup lang="ts">
  import { RadioGroup } from '@ark-ui/vue/radio-group';

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
  <RadioGroup.Root
    v-model="value"
    class="relative gap-4 flex flex-col"
  >
    <RadioGroup.Label class="text-sm font-medium">
      {{ label }}
    </RadioGroup.Label>
    <RadioGroup.Indicator />
    <RadioGroup.Item
      v-for="option in options"
      :key="option.label"
      :value="option.value"
      class="flex items-center gap-3 cursor-pointer"
    >
      <RadioGroup.ItemControl
        class="w-5 h-5 rounded-full border data-[state=checked]:outline data-[state=checked]:outline-4 data-[state=checked]:-outline-offset-8 data-[state=checked]:border-gray-700"
      />
      <RadioGroup.ItemText class="text-md font-medium">{{ option.label }}</RadioGroup.ItemText>
      <RadioGroup.ItemHiddenInput />
    </RadioGroup.Item>
  </RadioGroup.Root>
</template>

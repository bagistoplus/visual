<script setup lang="ts">
  import { Checkbox } from '@ark-ui/vue/checkbox';

  const checked = defineModel<boolean>({ default: false })
  const props = defineProps<{
    label?: string;
  }>();
</script>

<template>
  <Checkbox.Root
    v-model:checked="checked"
    class="gap-2 flex items-center"
  >
    <Checkbox.Control :class="[
      'w-4 h-4 rounded-sm border bg-white border-zinc-400 cursor-pointer flex items-center justify-center text-white',
      'data-[state=checked]:bg-gray-700 data-[state=checked]:border-gray-700'
    ]">
      <Checkbox.Indicator>
        <i-heroicons-check class="w-3" />
      </Checkbox.Indicator>
    </Checkbox.Control>
    <Checkbox.Label v-if="label">{{ props.label }}</Checkbox.Label>
    <Checkbox.HiddenInput />
  </Checkbox.Root>
</template>

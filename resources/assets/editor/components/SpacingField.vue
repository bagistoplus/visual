<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { NumberInput } from '@ark-ui/vue/number-input';
import HeroiconsLink from '~icons/heroicons/link';
import HeroiconsLinkSlash from '~icons/heroicons/link-slash';
import ArrowLongUp from '~icons/heroicons/arrow-long-up';
import ArrowLongDown from '~icons/heroicons/arrow-long-down';
import ArrowLongLeft from '~icons/heroicons/arrow-long-left';
import ArrowLongRight from '~icons/heroicons/arrow-long-right';


const sides = ['top', 'right', 'bottom', 'left'] as const
const icons = {
  top: ArrowLongUp,
  right: ArrowLongRight,
  bottom: ArrowLongDown,
  left: ArrowLongLeft,
};

interface SpacingValue {
  top: number;
  right: number;
  bottom: number;
  left: number;
}

interface Props {
  field: PropertyField;
}

defineProps<Props>();

const modelValue = defineModel<SpacingValue | null>();

const defaultSpacing: SpacingValue = {
  top: 0,
  right: 0,
  bottom: 0,
  left: 0,
};

const model = computed({
  get: () => modelValue.value || defaultSpacing,
  set: (value: SpacingValue) => {
    modelValue.value = value;
  },
});

const isLinked = ref(true);

function updateValue(side: keyof SpacingValue, value: number) {
  if (isLinked.value) {
    model.value = {
      top: value,
      right: value,
      bottom: value,
      left: value,
    };
  } else {
    model.value = {
      ...model.value,
      [side]: value,
    };
  }
}

function toggleLink() {
  isLinked.value = !isLinked.value;

  if (isLinked.value) {
    const topValue = model.value.top;
    model.value = {
      top: topValue,
      right: topValue,
      bottom: topValue,
      left: topValue,
    };
  }
}

onMounted(() => {
  if (model.value) {
    isLinked.value = model.value.top === model.value.right
      && model.value.top === model.value.bottom
      && model.value.top === model.value.left
  }
});
</script>

<template>
  <div class="flex flex-col gap-1">
    <div
      v-if="field.label"
      class="flex items-center justify-between"
    >
      <label class="text-sm font-medium text-gray-700">
        {{ field.label }}
      </label>
    </div>

    <div class="flex border border-gray-300 rounded overflow-hidden divide-x">
      <div class="grid grid-cols-4 divide-x ">
        <NumberInput.Root
          class="flex w-full relative group items-center gap-0.5 px-0.5"
          v-for="side in sides"
          :model-value="String(model[side])"
          :min="field.min"
          :max="field.max"
          @update:model-value="updateValue(side, Number($event))"
        >
          <component
            :is="icons[side]"
            class="w-4 h-4 text-gray-600 flex-none"
          />
          <NumberInput.Input class="flex-1 w-full border-none outline-none text-sm py-1.5" />
          <NumberInput.Control class="absolute top-0 right-1 bottom-0 flex flex-col hidden group-hover:flex">
            <NumberInput.IncrementTrigger class="flex-1 flex items-center">
              <i-heroicons-chevron-up class="w-3 h-3 text-gray-600" />
            </NumberInput.IncrementTrigger>
            <NumberInput.DecrementTrigger class="flex-1 flex items-center">
              <i-heroicons-chevron-down class="w-3 h-3 text-gray-600" />
            </NumberInput.DecrementTrigger>
          </NumberInput.Control>
        </NumberInput.Root>
      </div>
      <button
        type="button"
        class="flex-none p-1 rounded hover:bg-gray-100 transition-colors"
        :class="{ 'text-blue-600': isLinked, 'text-gray-400': !isLinked }"
        @click="toggleLink"
      >
        <HeroiconsLink
          v-if="isLinked"
          class="w-4 h-4"
        />
        <HeroiconsLinkSlash
          v-else
          class="w-4 h-4"
        />
      </button>
    </div>
  </div>
</template>
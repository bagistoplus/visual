<script setup lang="ts">
import { Popover } from '@ark-ui/vue/popover';
import useI18n from '../composables/i18n';
import OptionListbox from './OptionListbox.vue';

interface Option {
  value: string;
  label: string;
}

interface Props {
  options: Option[];
  multiple?: boolean;
  placeholder?: string;
  disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  multiple: false,
  placeholder: '',
  disabled: false,
});

/** One value for a plain picker, a list of them for one that holds several. */
const model = defineModel<string | string[] | null>();

const { t } = useI18n();

const opened = ref(false);

/**
 * A list long enough to search is long enough to be worth reading in one go,
 * which is what a category list is and a handful of sort orders is not.
 */
const wide = computed(() => props.multiple || props.options.length > 8);

const chosen = computed<string[]>(() => {
  if (Array.isArray(model.value)) {
    return model.value;
  }

  return model.value ? [String(model.value)] : [];
});

/** What the box reads while it is closed: what was chosen, or what to choose. */
const label = computed(() =>
  chosen.value
    .map((value) => props.options.find((option) => option.value === value)?.label ?? value)
    .join(', ')
);

function onSelect() {
  // A picker holding several stays open, so the rest of the list can be picked
  // from without opening it again.
  if (!props.multiple) {
    opened.value = false;
  }
}
</script>

<template>
  <Popover.Root v-model:open="opened">
    <Popover.Trigger
      as-child
      :disabled="disabled"
    >
      <div
        class="flex h-9 w-full min-w-0 cursor-pointer items-center gap-2 rounded border px-2 text-sm data-[disabled]:cursor-not-allowed data-[disabled]:opacity-50"
      >
        <span
          class="w-0 flex-1 truncate"
          :class="{ 'text-gray-400': !label }"
        >
          {{ label || placeholder || t('Select') }}
        </span>

        <i-heroicons-chevron-down class="w-4 flex-none text-gray-400" />
      </div>
    </Popover.Trigger>

    <Popover.Positioner
      class="min-w-[var(--reference-width)] max-w-[var(--available-width)] !z-50"
      :class="wide ? 'w-72' : 'w-[var(--reference-width)]'"
    >
      <Popover.Content class="flex max-h-80 flex-col rounded-lg border bg-white shadow outline-none">
        <OptionListbox
          v-model="model"
          :options="options"
          :multiple="multiple"
          @select="onSelect"
        />
      </Popover.Content>
    </Popover.Positioner>
  </Popover.Root>
</template>

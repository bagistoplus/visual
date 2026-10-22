<script setup lang="ts">
import useI18n from '../composables/i18n';

interface Option {
  value: string;
  label: string;
}

interface Props {
  options: Option[];
  multiple?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  select: [value: string];
}>();

const { t } = useI18n();

/** One value for a plain picker, a list of them for one that holds several. */
const model = defineModel<string | string[] | null>();

const search = ref('');

const chosen = computed<string[]>(() => {
  if (Array.isArray(model.value)) {
    return model.value;
  }

  return model.value ? [String(model.value)] : [];
});

const options = computed(() => {
  const term = search.value.trim().toLowerCase();

  if (!term) {
    return props.options;
  }

  return props.options.filter((option) => option.label.toLowerCase().includes(term));
});

function isSelected(option: Option): boolean {
  return chosen.value.includes(option.value);
}

function onItemClick(option: Option) {
  if (!props.multiple) {
    model.value = option.value;
    emit('select', option.value);

    return;
  }

  model.value = isSelected(option)
    ? chosen.value.filter((value) => value !== option.value)
    : [...chosen.value, option.value];

  emit('select', option.value);
}
</script>

<template>
  <div class="flex flex-col overflow-y-hidden">
    <div
      v-if="search || props.options.length > 2"
      class="mx-2 my-2 flex items-center gap-3 rounded-lg border px-3 py-1 focus-within:ring-2 focus-within:ring-zinc-700"
    >
      <i-heroicons-magnifying-glass class="h-4 w-4" />
      <input
        v-model="search"
        type="text"
        class="w-0 flex-1 text-zinc-600 focus:outline-none"
        :placeholder="t('Search')"
      >
    </div>

    <div class="flex-1 overflow-y-auto border-t">
      <p
        v-if="options.length === 0"
        class="px-3 py-2 text-sm text-zinc-500"
      >
        {{ t('No match') }}
      </p>

      <a
        v-for="option in options"
        :key="option.value"
        href="#"
        class="flex items-center gap-3 px-3 py-2 text-sm outline-none hover:bg-neutral-200"
        @click.stop.prevent="onItemClick(option)"
      >
        <span
          class="w-0 flex-1 truncate"
          :class="{ 'font-medium text-zinc-900': isSelected(option) }"
        >
          {{ option.label }}
        </span>

        <i-heroicons-check
          v-if="isSelected(option)"
          class="h-4 w-4 flex-none text-blue-600"
        />
      </a>
    </div>
  </div>
</template>

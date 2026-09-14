<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import useI18n from '../composables/i18n';
import SelectInput from './SelectInput.vue';

interface FilterOption {
  value: string;
  label: string;
}

interface FilterKey extends FilterOption {
  options?: FilterOption[];
  multiple?: boolean;
}

interface FilterRow {
  key: string;
  value: string;
}

interface Props {
  field: PropertyField & { keys?: FilterKey[]; addLabel?: string };
}

const props = defineProps<Props>();

const model = defineModel<Record<string, any> | null>();

const { t } = useI18n();

const keys = computed<FilterKey[]>(() => props.field.keys ?? []);

/**
 * The stored map as the rows it is edited through. Bagisto stores a filter as a
 * key and a value the product api is queried with, so the rows are the map's
 * entries and their order is the map's order.
 */
const rows = computed<FilterRow[]>(() =>
  Object.entries(model.value ?? {}).map(([key, value]) => ({
    key,
    value: value === null || value === undefined ? '' : String(value),
  }))
);

const allKeysUsed = computed(() => rows.value.length >= keys.value.length);

function declaredKey(key: string): FilterKey | undefined {
  return keys.value.find((candidate) => candidate.value === key);
}

/** The keys still free, plus the one this row already holds. */
function keysFor(index: number): FilterKey[] {
  const taken = rows.value.filter((_, position) => position !== index).map((row) => row.key);

  return keys.value.filter((key) => !taken.includes(key.value));
}

/**
 * The choices a value shows, with whatever is stored kept at the front so a
 * value the list no longer offers still reads back instead of blank.
 */
function optionsFor(row: FilterRow): FilterOption[] {
  const options = declaredKey(row.key)?.options ?? [];

  if (options.length === 0 || row.value === '' || options.some((option) => option.value === row.value)) {
    return options;
  }

  return [{ value: row.value, label: row.value }, ...options];
}

function labelOf(row: FilterRow): string {
  return declaredKey(row.key)?.label ?? row.key;
}

function isMultiple(row: FilterRow): boolean {
  return declaredKey(row.key)?.multiple === true;
}

/** A filter holding several values is stored as the comma separated list the api reads. */
function listValue(row: FilterRow): string[] {
  return row.value
    .split(',')
    .map((value) => value.trim())
    .filter(Boolean);
}

function write(entries: FilterRow[]) {
  const map: Record<string, string> = {};

  entries.forEach((entry) => {
    if (entry.key) {
      map[entry.key] = entry.value;
    }
  });

  model.value = map;
}

function replace(index: number, entry: FilterRow) {
  const entries = [...rows.value];
  entries[index] = entry;

  write(entries);
}

function changeKey(index: number, key: string) {
  const options = declaredKey(key)?.options ?? [];

  replace(index, { key, value: options[0]?.value ?? '' });
}

function changeValue(index: number, value: string | string[]) {
  replace(index, { key: rows.value[index].key, value: Array.isArray(value) ? value.join(',') : value });
}

function remove(index: number) {
  write(rows.value.filter((_, position) => position !== index));
}

function add() {
  const taken = rows.value.map((row) => row.key);
  const next = keys.value.find((key) => !taken.includes(key.value));

  if (!next) {
    return;
  }

  write([...rows.value, { key: next.value, value: next.options?.[0]?.value ?? '' }]);
}
</script>

<template>
  <div class="flex flex-col gap-2">
    <label
      v-if="field.label"
      class="text-sm font-medium text-gray-700"
    >
      {{ field.label }}
    </label>

    <div
      v-for="(row, index) in rows"
      :key="index"
      data-filter-row
      class="flex items-start gap-2"
    >
      <SelectInput
        class="flex-1"
        :options="keysFor(index)"
        :model-value="row.key"
        @update:model-value="changeKey(index, $event as string)"
      />

      <SelectInput
        v-if="isMultiple(row)"
        multiple
        class="flex-1"
        :options="optionsFor(row)"
        :placeholder="labelOf(row)"
        :model-value="listValue(row)"
        @update:model-value="changeValue(index, $event as string[])"
      />

      <SelectInput
        v-else-if="optionsFor(row).length"
        class="flex-1"
        :options="optionsFor(row)"
        :placeholder="labelOf(row)"
        :model-value="row.value"
        @update:model-value="changeValue(index, $event as string)"
      />

      <input
        v-else
        data-filter="text"
        type="text"
        class="h-9 min-w-0 flex-1 rounded border px-2 outline-none focus:border-blue-500"
        :value="row.value"
        @input="changeValue(index, ($event.target as HTMLInputElement).value)"
      />

      <button
        type="button"
        class="flex h-9 w-4 shrink-0 items-center justify-center text-gray-400 hover:text-red-600"
        :title="t('Remove filter')"
        @click="remove(index)"
      >
        <i-lucide-x class="size-3.5" />
      </button>
    </div>

    <button
      type="button"
      class="h-8 rounded border px-3 text-sm disabled:cursor-not-allowed disabled:opacity-50"
      :disabled="allKeysUsed"
      :title="allKeysUsed ? t('Every filter is already in use') : ''"
      @click="add"
    >
      {{ field.addLabel || t('Add filter') }}
    </button>
  </div>
</template>

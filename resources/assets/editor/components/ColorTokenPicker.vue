<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Popover } from '@ark-ui/vue/popover';
import {
  COLOR_TOKEN_EMPTY_VALUE,
  useColorSchemeContext,
} from '../composables/useColorSchemeContext';

interface Props {
  field: PropertyField;
}

const props = defineProps<Props>();

const model = defineModel<string | null>();
const opened = ref(false);

const { activeSchemeId, activeSchemeTokens, colorTokenOptions, resolveTokenColor } =
  useColorSchemeContext();

const schemeAvailable = computed(() => activeSchemeId.value !== null && activeSchemeTokens.value !== null);

const noneLabel = computed<string | null>(() => {
  const raw = props.field.allowNone;
  return typeof raw === 'string' ? raw : null;
});

const isNoneAllowed = computed(() => noneLabel.value !== null);
const isNoneSelected = computed(() => model.value === COLOR_TOKEN_EMPTY_VALUE);
const isTokenSelected = computed(
  () => typeof model.value === 'string' && model.value !== '' && !isNoneSelected.value,
);

const selectedTokenColor = computed(() => {
  if (!isTokenSelected.value) {
    return null;
  }
  return resolveTokenColor(model.value as string);
});

function formatTokenLabel(token: string): string {
  return token.charAt(0).toUpperCase() + token.slice(1);
}

function onSelectToken(token: string) {
  model.value = token;
  opened.value = false;
}

function onSelectNone() {
  model.value = COLOR_TOKEN_EMPTY_VALUE;
  opened.value = false;
}

function clearSelection(event: Event) {
  event.stopPropagation();
  model.value = null;
}
</script>

<template>
  <div>
    <label class="text-sm font-medium mb-1 text-gray-700">
      {{ field.label }}
    </label>

    <Popover.Root v-model:open="opened">
      <Popover.Trigger class="border border-zinc-300 rounded w-full flex justify-between h-10 px-3 items-center">
        <div
          v-if="isNoneSelected"
          class="flex flex-1 gap-3 items-center"
        >
          <span class="w-6 h-6 rounded flex-none border border-zinc-200 bg-white" />
          <span class="flex-1 text-left">{{ noneLabel }}</span>
          <button
            class="ml-auto flex-none mr-1 p-1 rounded-lg hover:bg-zinc-100"
            type="button"
            @click="clearSelection"
          >
            <i-heroicons-x-mark class="w-4 h-4" />
          </button>
        </div>
        <div
          v-else-if="isTokenSelected"
          class="flex flex-1 gap-3 items-center"
        >
          <span
            v-if="selectedTokenColor"
            class="w-6 h-6 rounded flex-none border border-zinc-200"
            :style="{ backgroundColor: selectedTokenColor }"
          />
          <span
            v-else
            class="w-6 h-6 rounded flex-none border border-zinc-200 bg-zinc-100 relative overflow-hidden"
            :title="schemeAvailable ? 'Token missing from active scheme' : 'No active color scheme'"
          >
            <span class="absolute inset-0 flex items-center justify-center text-zinc-400">
              <i-heroicons-no-symbol class="w-4 h-4" />
            </span>
          </span>
          <span class="flex-1 text-left">{{ formatTokenLabel(model as string) }}</span>
          <button
            class="ml-auto flex-none mr-1 p-1 rounded-lg hover:bg-zinc-100"
            type="button"
            @click="clearSelection"
          >
            <i-heroicons-x-mark class="w-4 h-4" />
          </button>
        </div>
        <span
          v-else
          class="text-zinc-500"
        >Select a color token</span>
        <i-heroicons-chevron-up-down class="flex-none h-4 w-4" />
      </Popover.Trigger>

      <Popover.Positioner>
        <Popover.Content class="border border-zinc-300 rounded shadow-md w-[var(--reference-width)] z-50 bg-white -mt-1 overflow-y-auto max-h-[362px]">
          <button
            v-for="token in colorTokenOptions"
            :key="token"
            type="button"
            class="w-full flex items-center gap-3 px-3 py-2 hover:bg-zinc-100 text-left relative"
            :class="{ 'bg-zinc-100': model === token }"
            @click="onSelectToken(token)"
          >
            <span
              v-if="resolveTokenColor(token)"
              class="w-5 h-5 rounded flex-none border border-zinc-200"
              :style="{ backgroundColor: resolveTokenColor(token)! }"
            />
            <span
              v-else
              class="w-5 h-5 rounded flex-none border border-zinc-200 bg-zinc-100 relative overflow-hidden"
            >
              <span class="absolute inset-0 flex items-center justify-center text-zinc-400">
                <i-heroicons-no-symbol class="w-3 h-3" />
              </span>
            </span>
            <span class="flex-1">{{ formatTokenLabel(token) }}</span>
            <i-heroicons-check-circle-solid
              v-if="model === token"
              class="w-5 h-5 text-green-400"
            />
          </button>

          <template v-if="isNoneAllowed">
            <div class="border-t border-zinc-200" />
            <button
              type="button"
              class="w-full flex items-center gap-3 px-3 py-2 hover:bg-zinc-100 text-left relative"
              :class="{ 'bg-zinc-100': isNoneSelected }"
              @click="onSelectNone"
            >
              <span class="w-5 h-5 rounded flex-none border border-zinc-200 bg-white" />
              <span class="flex-1">{{ noneLabel }}</span>
              <i-heroicons-check-circle-solid
                v-if="isNoneSelected"
                class="w-5 h-5 text-green-400"
              />
            </button>
          </template>
        </Popover.Content>
      </Popover.Positioner>
    </Popover.Root>
  </div>
</template>

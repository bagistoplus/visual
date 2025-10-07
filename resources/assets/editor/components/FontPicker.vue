<script setup lang="ts">
import type { PropertyField } from '@craftile/types';
import { Button } from '@craftile/editor/ui';
import { Dialog } from '@ark-ui/vue/dialog';
import { Field } from '@ark-ui/vue/field';
import useI18n from '../composables/i18n';

interface Font {
  slug: string;
  name: string;
  weights: string[];
  styles: string[];
}

interface Props {
  field: PropertyField;
}

const { t } = useI18n();
const model = defineModel({
  get(v: string | Font | null): Font | null {
    if (!v) {
      return null;
    }

    if (typeof v !== 'string') {
      return v;
    }

    return { slug: v, name: v.replace(/-/g, ' '), weights: [], styles: [] };
  },
});

defineProps<Props>();

const initialValue = ref<Font | null>(null);
const isDirty = ref(false);
const opened = ref(false);
const search = ref('');
const isFetching = ref(false);
const fonts = ref<Font[]>([]);

const fetchFonts = async () => {
  isFetching.value = true;
  try {
    const response = await fetch('https://fonts.bunny.net/list');
    const data = await response.json();

    const formatted: Font[] = [];
    Object.keys(data).forEach((key) => {
      formatted.push({
        slug: key,
        name: key.replace(/-/g, ' '),
        ...(data as any)[key],
      });
    });

    fonts.value = formatted;
  } catch (error) {
    console.error('Failed to fetch fonts:', error);
  } finally {
    isFetching.value = false;
  }
};

onMounted(() => fetchFonts());

const displayedFonts = computed(() => {
  if (!fonts.value) {
    return [];
  }

  if (!search.value) {
    return fonts.value.slice(0, 20);
  }

  return fonts.value
    .filter((font: any) => font.name.toLowerCase().includes(search.value.toLowerCase()))
    .slice(0, 20);
});

const loadedFonts = ref(new Set());
const observers = new Map<string, IntersectionObserver>();

function onSelectFont(font: Font) {
  model.value = font;
  isDirty.value = true;
}

function handleFontElement(el: HTMLElement, font: any) {
  if (loadedFonts.value.has(font.slug) || observers.has(font.slug)) {
    return;
  }

  const observer = new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) {
      loadFont(font);
      observer.disconnect();
      observers.delete(font.slug);
    }
  }, { threshold: 0.1 });

  observer.observe(el);
  observers.set(font.slug, observer);
}

function loadFont(font: any) {
  const linkId = `font-${font.slug}`;

  if (!document.getElementById(linkId)) {
    const link = document.createElement('link');
    link.id = linkId;
    link.rel = 'stylesheet';
    link.href = createFontLink(font);
    document.head.appendChild(link);
    loadedFonts.value.add(font.slug);
  }
}

function createFontLink(font: any) {
  return `https://fonts.bunny.net/css?display=fallback&family=${font.slug}`;
}

function onOpenChange() {
  isDirty.value = false;
  initialValue.value = model.value as Font | null
}

function onCancel() {
  opened.value = false;
  model.value = initialValue.value;
  isDirty.value = false;
}
</script>

<template>
  <div>
    <label class="text-sm font-medium mb-1 text-gray-700">
      {{ field.label }}
    </label>
    <Dialog.Root
      v-model:open="opened"
      :modal="false"
      :close-on-interact-outside="false"
      @open-change="onOpenChange"
    >
      <Dialog.Trigger class="flex items-center px-3 py-2.5 gap-4 border rounded w-full">
        <i-ri-font-size class="flex-none w-4 h-4 text-zinc-500" />
        <span class="flex-1 text-left capitalize line-clamp-1">{{ model?.name }}</span>
        <i-heroicons-chevron-up-down class="flex-none w-4 h-4 text-zinc-500" />
      </Dialog.Trigger>
      <Dialog.Positioner class="flex absolute z-50 inset-0 w-full h-full items-center justify-center">
        <Dialog.Content class="bg-white shadow flex flex-col w-full h-full overflow-hidden">
          <header class="flex-none h-12 border-b border-neutral-200 flex gap-3 px-4 items-center justify-between">
            <Dialog.Title>{{ t('Font Picker') }}</Dialog.Title>
            <button
              @click="onCancel"
              class="cursor-pointer rounded-lg p-0.5 text-neutral-700 hover:bg-neutral-300"
            >
              <i-heroicons-x-mark class="w-5 h-5" />
            </button>
          </header>
          <div class="flex-1 flex flex-col  min-h-0  overflow-y-auto">
            <div class="p-3">
              <Field.Root class="relative">
                <i-heroicons-magnifying-glass class="absolute left-3 top-2.5 text-zinc-500" />
                <Field.Input
                  v-model="search"
                  class="w-full pr-3 pl-9 h-10 text-gray-500 rounded border border-zinc-300 focus:outline-none focus:ring-2 focus:ring focus:ring-zinc-700 focus:border-transparent"
                  placeholder="Search"
                />
              </Field.Root>
            </div>

            <div
              v-if="isFetching"
              class="flex items-center justify-center py-6"
            >
              <i-lucide-loader-2 class="w-6 h-6 animate-spin text-zinc-500" />
            </div>
            <ul
              v-else
              class="divide-y"
            >
              <li
                v-for="(font) in displayedFonts"
                :ref="(el: HTMLElement) => handleFontElement(el, font)"
                class="p-3 first:border-t hover:bg-zinc-100 cursor-pointer capitalize flex justify-between items-center"
                :class="{ 'bg-zinc-100': model?.slug === font.slug }"
                :style="{ fontFamily: loadedFonts.has(font.slug) ? font.name : 'inherit' }"
                @click="onSelectFont(font)"
              >
                <span>{{ font.name }}</span>
                <i-heroicons-check-circle-solid
                  v-if="model?.slug === font.slug"
                  class="w-5 h-5 text-zinc-700"
                />
              </li>
            </ul>
          </div>
          <footer class="flex-none flex items-center gap-3 p-3 justify-end h-12 border-t border-neutral-200">
            <Button @click="onCancel">{{ t('Cancel') }}</Button>
            <Dialog.CloseTrigger as-child>
              <Button
                variant="primary"
                :disabled="!isDirty"
              >
                {{ t('Select') }}
              </Button>
            </Dialog.CloseTrigger>
          </footer>
        </Dialog.Content>
      </Dialog.Positioner>
    </Dialog.Root>
  </div>
</template>

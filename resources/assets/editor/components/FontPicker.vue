<script setup lang="ts">
  import { Dialog } from '@ark-ui/vue/dialog';
  import { Field } from '@ark-ui/vue/field';
  import { useFetch } from '@vueuse/core';
  import { useIntersectionObserver } from '@vueuse/core';

  interface Props {
    label?: string;
  }

  interface Font {
    slug: string;
    name: string;
    weights: string[];
    styles: string[];
  }

  const props = defineProps<Props>();
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

  const initialValue = ref<Font | null>(null);
  const isDirty = ref(false);
  const opened = ref(false);
  const search = ref('');
  const { isFetching, data: fonts } = useFetch('https://fonts.bunny.net/list', {
    afterFetch({ data }) {
      const formatted: any[] = [];
      Object.keys(data).forEach((key) => {
        formatted.push({
          slug: key,
          name: key.replace(/-/g, ' '),
          ...(data as any)[key],
        });
      });

      return { data: formatted };
    },
  }).json();

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
  const observers = new Map();

  function onSelectFont(font: Font) {
    model.value = font;
    isDirty.value = true;
  }

  function handleFontElement(el: HTMLElement, font: any) {
    if (loadedFonts.value.has(font.slug) || observers.has(font.slug)) {
      return;
    }

    const observer = useIntersectionObserver(el, ([entry]) => {
      if (entry.isIntersecting) {
        loadFont(font);
        observer.stop();
        observers.delete(font.slug);
      }
    }, { threshold: 0.1 });

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

  function onCancel() {
    opened.value = false;
    model.value = initialValue.value;
    isDirty.value = false;
  }
</script>

<template>
  <div>
    <label
      v-if="label"
      class="text-sm mb-2"
    >{{ label }}</label>

    <Dialog.Root
      v-model:open="opened"
      :modal="false"
      :close-on-interact-outside="false"
      @open-change="isDirty = false; initialValue = model as Font | null"
    >
      <Dialog.Trigger class="flex items-center px-3 py-2.5 gap-4 border rounded w-full">
        <i-ri-font-size class="flex-none w-4 h-4 text-gray-500" />
        <span class="flex-1 text-left capitalize">{{ model?.name }}</span>
        <i-heroicons-chevron-up-down class="flex-none w-4 h-4 text-gray-500" />
      </Dialog.Trigger>
      <Dialog.Positioner class="flex fixed z-50 top-14 left-14 bottom-0 w-[19.9rem] items-center justify-center">
        <Dialog.Content class="bg-white shadow flex flex-col w-full h-full overflow-hidden">
          <header class="flex-none h-12 border-b border-neutral-200 flex gap-3 px-4 items-center justify-between">
            <Dialog.Title>{{ $t('Font Picker') }}</Dialog.Title>
            <Dialog.CloseTrigger class="cursor-pointer rounded-lg p-0.5 text-neutral-700 hover:bg-neutral-300">
              <i-heroicons-x-mark class="w-5 h-5" />
            </Dialog.CloseTrigger>
          </header>
          <div class="flex-1 flex flex-col  min-h-0  overflow-y-auto">
            <div class="p-3">
              <Field.Root class="relative">
                <i-heroicons-magnifying-glass class="absolute left-3 top-2.5 text-gray-500" />
                <Field.Input
                  v-model="search"
                  class="w-full pr-3 pl-9 h-10 text-surface-500 rounded border border-gray-300 focus:outline-none focus:ring focus:ring-gray-700"
                  placeholder="Search"
                />
              </Field.Root>
            </div>

            <div
              v-if="isFetching"
              class="flex items-center justify-center py-6"
            >
              <i-lucide-loader-2 class="w-6 h-6 animate-spin text-gray-500" />
            </div>
            <ul
              v-else
              class="divide-y"
            >
              <li
                v-for="(font) in displayedFonts"
                :ref="(el: HTMLElement) => handleFontElement(el, font)"
                class="p-3 first:border-t hover:bg-gray-100 cursor-pointer capitalize flex justify-between items-center"
                :class="{ 'bg-gray-100': model?.slug === font.slug }"
                :style="{ fontFamily: loadedFonts.has(font.slug) ? font.name : 'inherit' }"
                @click="onSelectFont(font)"
              >
                <span>{{ font.name }}</span>
                <i-heroicons-check-circle-solid
                  v-if="model?.slug === font.slug"
                  class="w-5 h-5 text-gray-700"
                />
              </li>
            </ul>
          </div>
          <footer class="flex-none flex items-center gap-3 p-3 justify-end h-12 border-t border-neutral-200">
            <button
              @click="onCancel"
              class="text-sm shadow px-3 py-1 rounded bg-neutral-100 border"
            >{{ $t('Cancel') }}</button>
            <Dialog.CloseTrigger
              class="text-sm shadow px-3 py-1 rounded bg-gray-800 text-white border hover:bg-gray-700"
              :class="{ 'opacity-40 cursor-not-allowed': !isDirty }"
              :disabled="!isDirty"
            >Select</Dialog.CloseTrigger>
          </footer>
        </Dialog.Content>
      </Dialog.Positioner>
    </Dialog.Root>
  </div>
</template>
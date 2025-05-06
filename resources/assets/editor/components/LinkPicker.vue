<script setup lang="ts">
  import { Combobox, createListCollection, useCombobox } from '@ark-ui/vue/combobox';
  import { Category, Product } from '../types';
  import { CmsPage } from '../types';

  const model = defineModel<string | null>();

  const valueType = ref('link');
  const realLink = ref();

  const combobox = useCombobox({
    collection: createListCollection({ items: [] }),
    openOnClick: true,
    openOnKeyPress: false,
    allowCustomValue: true
  });

  const activePanel = ref<string>('');
  onMounted(() => {
    parseModelValue();
  });

  function parseModelValue() {
    if (!model.value) {
      return;
    }

    if (!model.value.startsWith('visual://')) {
      valueType.value = 'link';
      combobox.value.setInputValue(model.value);
      realLink.value = model.value;
      return;
    }

    const matches = model.value.match(/^visual:\/\/([^:]+):([^\/]+)\/(.*)?$/);

    if (matches) {
      valueType.value = matches[1];
      combobox.value.setInputValue(decodeURIComponent(matches[2]));
      realLink.value = computeRealLink(matches[3], matches[1] === 'cms_pages' ? 'page/' : '');
    }
  }

  function computeRealLink(slug: string, path: string = '') {
    const url = new URL(path + slug, new URL(window.ThemeEditor.storefrontUrl).origin);

    return url.href;
  }

  function onCategorySelected(category: Category) {
    model.value = 'visual://categories:' + encodeURIComponent(category.name) + '/' + category.slug;
    combobox.value.setInputValue(category.name);
    valueType.value = 'categories';
    realLink.value = computeRealLink(category.slug);
    combobox.value.setOpen(false);
  }

  function onProductSelected(product: Product) {
    model.value = 'visual://products:' + encodeURIComponent(product.name) + '/' + product.url_key;
    combobox.value.setInputValue(product.name);
    valueType.value = 'products';
    realLink.value = computeRealLink(product.url_key);
    combobox.value.setOpen(false);
  }

  function onPageSelected(page: CmsPage) {
    model.value = 'visual://cms_pages:' + encodeURIComponent(page.page_title) + '/' + page.url_key;
    combobox.value.setInputValue(page.page_title);
    valueType.value = 'cms_pages';
    realLink.value = computeRealLink(page.url_key, 'page/');
    combobox.value.setOpen(false);
  }

  function onInput(event: Event) {
    try {
      const url = new URL((event.target as HTMLInputElement).value);
      valueType.value = 'link';
      model.value = url.href;
      realLink.value = url.href;
      combobox.value.setInputValue(url.href);
    } catch (e) {
      parseModelValue();
    }

  }
  function onClear() {
    combobox.value.setInputValue('');
    valueType.value = 'link';
    realLink.value = '';
    model.value = '';
  }
</script>
<template>
  <div>
    <Combobox.RootProvider
      :value="combobox"
      class="mt-1 gap-2 flex flex-col relative"
    >
      <a
        v-if="realLink"
        :href="realLink"
        target="_blank"
        class="absolute right-0 -top-6"
      >
        <i-heroicons-arrow-top-right-on-square-solid class="w-4 h-4" />
      </a>
      <Combobox.Control
        class="flex border px-3 h-10 gap-3 text-sm w-full cursor-pointer rounded outline-0 items-center appearance-none justify-between focus-within:shadow focus-within:ring focus-within:ring-gray-700"
      >
        <i-bi-tags
          v-if="valueType === 'categories'"
          class="w-4 h-4 flex-none transform rotate-90"
        />
        <i-bi-tag
          v-else-if="valueType === 'products'"
          class="w-4 h-4 flex-none transform rotate-90"
        />
        <i-mdi-file-document-outline
          v-else-if="valueType === 'cms_pages'"
          class="w-4 h-4 flex-none"
        />
        <i-heroicons-link
          v-else
          class="w-4 h-4 flex-none"
        />

        <Combobox.Input
          class="outline-none flex-1 w-0"
          @input="combobox.setOpen(false)"
          @blur="onInput"
        />
        <button
          v-if="model"
          class="flex-none text-gray-700 hover:bg-gray-200 p-1 rounded-lg"
          @click="onClear"
        >
          <i-heroicons-x-mark clip="w-4 h-4" />
        </button>
      </Combobox.Control>

      <Combobox.Positioner class="w-[var(--reference-width)] !z-10">
        <Combobox.Content class="bg-white rounded-lg shadow gap-1 flex flex-col max-h-96 border data-[state=open]:animate-fade-in">
          <div v-if="!activePanel">
            <button
              class="appearance-none w-full h-9 px-3 flex gap-3 items-center hover:bg-gray-200"
              @click.prevent="activePanel = 'categories'"
            >
              <i-bi-tags class="w-4 h-4 transform rotate-90" />
              {{ $t('Categories') }}
            </button>
            <button
              class="appearance-none w-full h-9 px-3 flex gap-3 items-center hover:bg-gray-200"
              @click.prevent="activePanel = 'products'"
            >
              <i-bi-tag class="w-4 h-4 transform rotate-90" />
              {{ $t('Products') }}
            </button>
            <button
              class="appearance-none w-full h-9 px-3 flex gap-3 items-center hover:bg-gray-200"
              @click.prevent="activePanel = 'cms_pages'"
            >
              <i-mdi-file-document-outline class="w-4 h-4 flex-none text-gray-700" />
              {{ $t('Cms Pages') }}
            </button>
          </div>
          <div
            v-else
            class="flex flex-col h-full overflow-hidden"
          >
            <button
              class="h-9 flex-none bg-gray-200 flex gap-3 w-full items-center rounded-t-lg text-left px-3"
              @click="activePanel = ''"
            >
              <i-heroicons-arrow-left class="w-4 h-4" />
              {{ $t('Back') }}
            </button>
            <CategoryListbox
              v-if="activePanel === 'categories'"
              class="h-full flex-1"
              @update:modelValue="onCategorySelected"
            />
            <ProductListbox
              v-else-if="activePanel === 'products'"
              class="h-full flex-1"
              @update:modelValue="onProductSelected"
            />
            <CmsPageListbox
              v-else-if="activePanel === 'cms_pages'"
              class="h-full flex-1"
              @update:modelValue="onPageSelected"
            />
          </div>
        </Combobox.Content>
      </Combobox.Positioner>
    </Combobox.RootProvider>
  </div>
</template>
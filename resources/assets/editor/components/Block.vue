<script lang="ts" setup>
  import { useStore } from '../store';
  import { Block, BlockData } from '../types';

  const store = useStore();
  const router = useRouter();
  const route = useRoute('/[section]');

  const emit = defineEmits(['remove', 'toggle']);
  const props = defineProps<{ block: BlockData, schema: Block }>();

  const label = computed(() => {
    let text;

    for (const fn of [getProductLabel, getCategoryLabel, getPageLabel, getText, getTitle, getHeading]) {
      if (text = fn()) break;
    }

    return text ? props.block.name + ' - ' + text : props.block.name;
  })

  const getProductLabel = () => {
    const productSetting = props.schema.settings.find(s => s.type === 'product');
    if (!productSetting) {
      return null;
    }

    const product = store.getProduct(props.block.settings[productSetting.id] as number);
    return product?.name;
  }

  const getCategoryLabel = () => {
    const categorySetting = props.schema.settings.find(s => s.type === 'category');
    if (!categorySetting) {
      return null;
    }

    const category = store.getCategory(props.block.settings[categorySetting.id] as number);
    return category?.name;
  }

  const getPageLabel = () => {
    const pageSetting = props.schema.settings.find(s => s.type === 'cms_page');
    if (!pageSetting) {
      return null;
    }

    const page = store.getCmsPage(props.block.settings[pageSetting.id] as number);
    return page?.page_title;
  }

  const getTitle = () => {
    return props.block.settings.title;
  }

  const getHeading = () => {
    return props.block.settings.heading;
  }

  const getText = () => {
    const textSetting = props.schema.settings.find(s => s.type === 'text');
    if (!textSetting) {
      return null;
    }

    const text = props.block.settings[textSetting.id];
    return text ? sanitizeString(text as string) : null;
  }

  function open() {
    router.push({ name: '/[section].[block]', params: { block: props.block.id, section: route.params.section } });
  }

  function sanitizeString(input: string) {
    const doc = new DOMParser().parseFromString(input, 'text/html');
    const content = doc.body.textContent;
    return content === 'undefined' || content === 'null' ? input : content;
  }

</script>

<template>
  <div class="">
    <div
      class="flex rounded border border-zinc-100 hover:bg-zinc-100 cursor-pointer active:ring-inset active:ring-2 active:ring-gray-700 data-[disabled=true]:text-zinc-500"
      :data-disabled="block.disabled"
    >
      <button class="handle flex-none py-1 px-1 rounded-md hover:bg-zinc-200 cursor-move">
        <i-ri-draggable class="inline w-4" />
      </button>

      <div
        @click="open"
        class="group mx-2 py-1 pr-1 text-sm flex-1 flex items-center max-w-full"
      >
        <div class="w-0 flex-1 truncate text-xs capitalize">
          {{ label }}
        </div>
        <button
          @click.stop="emit('remove')"
          class="flex-none invisible group-hover:visible py-[1px] px-[2px] rounded-md hover:bg-zinc-200"
        >
          <i-heroicons-trash class="inline w-4" />
        </button>
        <button
          @click.stop="emit('toggle')"
          class="flex-none invisible group-hover:visible py-[1px] px-[2px] rounded-md hover:bg-zinc-200 ml-1"
        >
          <i-heroicons-eye
            v-if="!block.disabled"
            class="inline w-4"
          />
          <i-heroicons-eye-slash
            v-else
            class="inline w-4"
          />
        </button>
      </div>
    </div>
  </div>
</template>
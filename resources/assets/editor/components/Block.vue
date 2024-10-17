<script lang="ts" setup>
  import { BlockData } from '../types';

  const router = useRouter();
  const route = useRoute('/[section]');

  const emit = defineEmits(['remove', 'toggle']);
  const props = defineProps<{ block: BlockData }>();

  function open() {
    router.push({ name: '/[section].[block]', params: { block: props.block.id, section: route.params.section } });
  }
</script>

<template>
  <div class="">
    <div
      class="flex rounded border border-zinc-100 hover:bg-zinc-100 cursor-pointer active:ring-inset active:ring-2 active:ring-gray-700 data-[disabled=true]:text-zinc-500"
      :data-disabled="block.disabled"
    >
      <button class="handle flex-none py-1 px-1 rounded-md hover:bg-zinc-200 cursor-move">
        <i-heroicons-arrows-up-down class="inline w-3" />
      </button>

      <div
        @click="open"
        class="group mx-2 py-1 pr-1 text-sm flex-1 flex items-center max-w-full"
      >
        <div class="w-0 flex-1 truncate text-xs capitalize">
          {{ block.settings.title || block.settings.heading || block.settings.text || block.name }}
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

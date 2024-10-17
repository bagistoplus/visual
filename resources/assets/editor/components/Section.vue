<script lang="ts" setup>
  import type { SectionData } from '../types';

  const router = useRouter();
  const emit = defineEmits<{
    (e: 'activate'): void;
    (e: 'deactivate'): void;
    (e: 'toggle'): void;
    (e: 'remove'): void;
  }>();
  const props = withDefaults(defineProps<{
    section: SectionData,
    static: boolean
  }>(), { static: false });

  function open() {
    router.push(props.section.id)
  }

</script>
<template>
  <div
    class=""
    @mouseenter="emit('activate')"
    @mouseleave="emit('deactivate')"
  >
    <div
      class="flex rounded border border-zinc-100 hover:bg-zinc-100 cursor-pointer active:ring-inset active:ring-2 active:ring-gray-700 data-[disabled=true]:text-zinc-500"
      :data-disabled="section.disabled"
    >
      <button class="handle flex-none py-2 px-1 rounded-md hover:bg-zinc-200 cursor-move">
        <i-heroicons-arrows-up-down class="inline w-3" />
      </button>

      <div
        @click="open"
        class="group mx-2 py-2 pr-1 text-sm flex-1 flex items-center max-w-full"
      >
        <div class="w-0 flex-1 truncate ">{{ props.section.name }}</div>

        <button
          v-if="!static"
          @click.stop="emit('remove')"
          class="flex-none cursor-pointer invisible group-hover:visible py-[1px] px-[2px] rounded-md hover:bg-zinc-200"
        >
          <i-heroicons-trash class="inline w-4" />
        </button>

        <button
          @click.stop="emit('toggle')"
          class="flex-none cursor-pointer invisible group-hover:visible py-[1px] px-[2px] rounded-md hover:bg-zinc-200 ml-1"
        >
          <i-heroicons-eye
            v-if="!section.disabled"
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

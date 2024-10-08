<script lang="ts" setup>
const emit = defineEmits(['remove', 'toggle'])
</script>

<template>
  <div class="">
    <div class="flex rounded hover:bg-zinc-100 cursor-pointer active:ring-inset active:ring-2">
      <div @click="toggle" class="group mx-2 py-1 pr-1 text-sm flex-1 flex items-center max-w-full">
        <div class="w-0 flex-1 truncate text-xs">Block name {{ Math.floor(Math.random() * 100) }}</div>
        <button @click.stop="emit('remove')" class="flex-none invisible group-hover:visible py-[1px] px-[2px] rounded-md hover:bg-zinc-200">
          <TrashIcon class="inline w-4"/>
        </button>
        <button @click.stop="emit('toggle')" class="flex-none invisible group-hover:visible py-[1px] px-[2px] rounded-md hover:bg-zinc-200 ml-1">
          <EyeIcon class="inline w-4"/>
        </button>
      </div>
    </div>

  </div>
</template>

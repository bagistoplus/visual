<script lang="ts" setup>
// @ts-ignore
import Sortable from 'sortablejs/modular/sortable.core.esm.js';
import type { BlockData } from '../types';

interface Props {
  blocks: BlockData[];
  order: string[];
};

const props = withDefaults(defineProps<Props>(), {
});

const emit =defineEmits<{
  (e: 'reorder', order: string[]): void;
  (e: 'toggleBlock', id: string): void;
  (e: 'removeBlock', id: string): void;
}>();

const sortable = useTemplateRef<HTMLElement>('sortable');

onMounted(() => {
  new Sortable(sortable.value, {
    animation: 150,
    ghostClass: 'sortable-ghost',

    onEnd({ newIndex, oldIndex }: { newIndex: number; oldIndex: number }) {
      const order = [...props.order];
      const moved = order.splice(oldIndex, 1)[0];

      order.splice(newIndex, 0, moved);
      emit('reorder', order);
    },
  });
});
</script>

<template>
  <div ref="sortable" class="space-y-1">
    <Block
      v-for="block in blocks"
      :block="block"
      @toggle="emit('toggleBlock', block.id)"
      @remove="emit('removeBlock', block.id)"
    />
  </div>
</template>

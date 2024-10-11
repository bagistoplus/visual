<script lang="ts" setup>
// @ts-ignore
import Sortable from 'sortablejs/modular/sortable.core.esm.js';
import type { SectionData } from '../types';

interface Props {
  title: string;
  sections: SectionData[];
  order?: string[];
  static?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
  order: () => [],
  static: false
});

const emit =defineEmits<{
  (e: 'reorder', order: string[]): void;
  (e: 'addSection', event: any): void;
  (e: 'activateSection', id: string): void;
  (e: 'deactivateSection', id: string): void;
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
function test() {alert('ok');}
</script>

<template>
  <div>
    <h2 class="text-sm font-medium pl-2">{{ title }}</h2>
    <div ref="sortable" class="space-y-1 mt-4">
      <Section
        v-for="section in sections"
        :section="section"
        @activate="emit('activateSection', section.id)"
        @deactivate="emit('deactivateSection', section.id)"
      />
    </div>
    <div class="space-y-2 mt-4" v-if="!props.static">
      <button
        class="block w-full !text-left"
        @click="($event: any) => emit('addSection', $event)">
          <PlusCircleIcon class="w-4 h-4 inline mr-1"/>
          Add Section
      </button>
    </div>
  </div>
</template>

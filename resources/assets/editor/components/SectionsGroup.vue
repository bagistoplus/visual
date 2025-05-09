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

  const emit = defineEmits<{
    (e: 'reorder', order: string[]): void;
    (e: 'reordering', ctx: { order: string[], sectionId: string }): void;
    (e: 'addSection', event: any): void;
    (e: 'toggleSection', id: string): void;
    (e: 'removeSection', id: string): void;
    (e: 'activateSection', id: string): void;
    (e: 'deactivateSection', id: string): void;
  }>();

  const sortableEl = useTemplateRef<HTMLElement>('sortable');

  onMounted(() => {
    if (props.static) {
      return;
    }

    new Sortable(sortableEl.value, {
      animation: 150,
      ghostClass: 'sortable-ghost',

      onChange({ oldIndex, newIndex }: { oldIndex: number, newIndex: number }) {
        const newOrder = reorderSection({ oldIndex, newIndex })
        emit('reordering', { order: newOrder, sectionId: props.order[oldIndex] })
      },

      onEnd({ newIndex, oldIndex }: { newIndex: number; oldIndex: number }) {
        const order = reorderSection({ oldIndex, newIndex });
        emit('reorder', order);
      },
    });
  });

  function reorderSection({ oldIndex, newIndex }: { oldIndex: number, newIndex: number }) {
    const order = [...props.order];
    const moved = order.splice(oldIndex, 1)[0];

    order.splice(newIndex, 0, moved);
    return order;
  }
</script>

<template>
  <div class="pb-3">
    <h2 class="text-sm font-semibold p-3">{{ title }}</h2>
    <div
      ref="sortable"
      class="space-y-1 mx-3"
    >
      <template v-if="sections.length > 0">
        <Section
          v-for="section in sections"
          :key="section.id"
          :static="static"
          :section="section"
          @toggle="emit('toggleSection', section.id)"
          @remove="emit('removeSection', section.id)"
          @activate="emit('activateSection', section.id)"
          @deactivate="emit('deactivateSection', section.id)"
        />
      </template>
    </div>

    <p
      v-if="static && sections.length === 0"
      class="text-sm px-3"
    >
      {{ $t('No sections') }}
    </p>

    <div
      class="space-y-2 mt-4 px-3"
      v-if="!props.static"
    >
      <button
        class="w-full text-sm text-blue-600 rounded-lg cursor-pointer outline-0 inline-flex items-center gap-2 px-2 py-1 hover:bg-gray-200 focus:ring focus:ring-gray-700"
        @click="($event: any) => emit('addSection', $event)"
      >
        <i-heroicons-plus-circle class="w-4 h-4 inline mr-1" />
        {{ $t('Add Section') }}
      </button>
    </div>
  </div>
</template>
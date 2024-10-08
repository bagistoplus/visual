<script setup lang="ts">
import Sortable from 'sortablejs/modular/sortable.core.esm.js';

const sortable = useTemplateRef<HTMLElement>('sortable');

onMounted(() => {
  new Sortable(sortable.value, {
    animation: 150,
    ghostClass: 'sortable-ghost',
    onEnd({ newIndex, oldIndex }) {
      console.log(newIndex, oldIndex);
    }
  });
});

</script>
<template>
  <div>
    <div class="space" ref="sortable">
      <Block v-for="i in 2"/>
    </div>
    <Button
      text
      severity="info"
      label="Add Block"
      class="block w-full !text-left text-xs mt-1"
      @click="$event => emit('addSection', $event)">
      <template #icon>
        <PlusCircleIcon class="w-4 h-4 inline mr-1"/>
      </template>
    </Button>
  </div>
</template>

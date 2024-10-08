
<template>
  <div>
    <Button text plain severity="primary" @click="toggle" class="min-w-48 !text-left focus:ring-0">
        <HomeIcon class="inline w-4 mr-2"/>
        Home page
        <ChevronDownIcon class="inline w-4 ml-2"/>
    </Button>

    <Popover ref="op">
      <div class="flex flex-col gap-1 w-56 -m-4">
        <a v-for="i in 10" href="#" class="inline-flex items-center hover:bg-zinc-100 rounded-md px-2 py-1">
          <HomeIcon class="inline w-4 mr-2"/>
          Home page
        </a>
      </div>
    </Popover>
  </div>
</template>

<script setup>
import { ref } from "vue";

const op = ref();
const selectedMember = ref(null);
const members = ref([
    { name: 'Amy Elsner', image: 'amyelsner.png', email: 'amy@email.com', role: 'Owner' },
    { name: 'Bernardo Dominic', image: 'bernardodominic.png', email: 'bernardo@email.com', role: 'Editor' },
    { name: 'Ioni Bowcher', image: 'ionibowcher.png', email: 'ioni@email.com', role: 'Viewer' }
]);

const toggle = (event) => {
    op.value.toggle(event);
}

const selectMember = (member) => {
    selectedMember.value = member;
    op.value.hide();
}
</script>

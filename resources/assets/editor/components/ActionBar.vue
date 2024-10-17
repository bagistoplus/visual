<script setup lang="ts">
  const route = useRoute();
  const sectionsPanelActive = computed(() => !['/settings'].includes(route.path))
</script>

<template>
  <div class="flex flex-col gap-1 p-1 bg">
    <RouterLink
      to="/"
      class="hover:bg-gray-100 h-12 flex items-center justify-center rounded-md"
      :class="{ 'bg-zinc-200 text-blue-800': sectionsPanelActive }"
    >
      <i-heroicons-square-3-stack-3d class="w-5" />
    </RouterLink>
    <RouterLink
      to="/settings"
      class="hover:bg-gray-100 h-12 flex items-center justify-center rounded-md"
      active-class="bg-zinc-200 text-blue-800"
    >
      <i-heroicons-cog-6-tooth class="w-5" />
    </RouterLink>
  </div>
</template>

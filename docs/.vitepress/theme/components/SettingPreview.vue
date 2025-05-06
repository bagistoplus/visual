<script setup lang="ts">
  const props = defineProps<{
    image: string;
    title?: string;
  }>();
</script>

<template>
  <div class="setting-preview">
    <label>Preview</label>
    <img
      :src="image"
      :alt="title || 'Setting preview'"
    />
  </div>
</template>

<style lang="css">
.setting-preview {
  margin-top: 2rem;
  position: relative;
  display: flex;
  justify-content: center;
  border-radius: 0.75rem;
  background-color: #f6f6f7;
  padding: 2rem 1rem 1rem 1rem;
  font-size: 0.875rem;
  line-height: 1.75rem;
  color: rgb(209, 213, 219);
}

.setting-preview label {
  position: absolute;
  top: 0px;
  left: 1rem;
  transform: translate(0, -50%);
  border-radius: 0.5rem;
  background-color: rgb(75, 85, 99);
  padding-left: 0.5rem;
  padding-right: 0.5rem;
  padding-top: 1px;
  padding-bottom: 1px;
}

.setting-preview img {
  width: 100%;
  max-width: 360px;
}

.dark .setting-preview {
  background-color: #161618;
}

/* @media (prefers-color-scheme: dark) {
  .setting-preview {
    background-color: #161618;
  }
} */
</style>
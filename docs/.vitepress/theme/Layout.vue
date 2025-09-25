<script setup>
  import DefaultTheme from "vitepress/theme";
</script>

<template>
  <DefaultTheme.Layout>
    <template #home-hero-image>
      <iframe
        class="video"
        src="https://www.youtube.com/embed/fB7ik1hOWMc?loop=1&playlist=fB7ik1hOWMc&start=7&modestbranding=1&showinfo=0&rel=0"
        title="Introducing Bagisto Visual – A New Way to Build Bagisto Stores Visually"
        frameborder="0"
        allow="autoplay; encrypted-media"
        allowfullscreen
      ></iframe>
    </template>
  </DefaultTheme.Layout>
</template>

<style scoped>
.video {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100%;
  aspect-ratio: 16/9;
  transform: translate(-50%, -50%);
}
</style>

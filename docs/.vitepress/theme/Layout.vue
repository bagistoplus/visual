<script setup>
import DefaultTheme from "vitepress/theme";
import { useData, useRoute } from 'vitepress';
import { computed } from 'vue';
import BlogHome from './components/BlogHome.vue';
import BlogPost from './components/BlogPost.vue';

const { frontmatter } = useData();
const route = useRoute();

const isBlogPost = computed(() => route.path.startsWith('/blog/') && route.path !== '/blog/');
const isLandingPage = computed(() => frontmatter.value.pageClass === 'landing');
</script>

<template>
  <BlogHome v-if="frontmatter.layout === 'blog'" />
  <BlogPost v-else-if="isBlogPost" />
  <DefaultTheme.Layout v-else :class="frontmatter.pageClass">
    <template v-if="!isLandingPage" #home-hero-image>
      <iframe
        class="video"
        src="https://www.youtube.com/embed/fB7ik1hOWMc?loop=1&playlist=fB7ik1hOWMc&start=7&modestbranding=1&showinfo=0&rel=0"
        title="Introducing Bagisto Visual – A New Way to Build Bagisto Stores Visually"
        frameborder="0"
        allow="autoplay; encrypted-media"
        allowfullscreen
      ></iframe>
    </template>
    <template #layout-top>
      <slot name="layout-top" />
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

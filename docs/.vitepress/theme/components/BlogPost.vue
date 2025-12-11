<script setup lang="ts">
import { useData, useRoute } from 'vitepress';
import { computed } from 'vue';
import { data as posts } from '../blog.data';

const { frontmatter, page } = useData();
const route = useRoute();

const currentIndex = computed(() => {
  return posts.findIndex((p) => p.url === route.path);
});

const currentPost = computed(() => {
  return posts[currentIndex.value];
});

const prevPost = computed(() => {
  return currentIndex.value < posts.length - 1 ? posts[currentIndex.value + 1] : null;
});

const nextPost = computed(() => {
  return currentIndex.value > 0 ? posts[currentIndex.value - 1] : null;
});
</script>

<template>
  <div class="blog-post">
    <div class="container">
      <a href="/introduction/getting-started" class="back-to-docs">← Back to Docs</a>

      <article>
        <header class="post-header">
          <time v-if="currentPost" class="post-date">{{ currentPost.date.string }}</time>
          <h1 class="post-title">{{ frontmatter.title }}</h1>

          <div v-if="frontmatter.author" class="post-author-section">
            <div class="author-info">
              <img
                v-if="frontmatter.gravatar"
                :src="`https://www.gravatar.com/avatar/${frontmatter.gravatar}?s=80`"
                :alt="frontmatter.author"
                class="author-avatar"
              />
              <div class="author-details">
                <div class="author-name">{{ frontmatter.author }}</div>
                <a
                  v-if="frontmatter.twitter"
                  :href="`https://twitter.com/${frontmatter.twitter.replace('@', '')}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="author-twitter"
                >
                  {{ frontmatter.twitter }}
                </a>
              </div>
            </div>
          </div>
        </header>

        <div class="post-content">
          <Content />
        </div>

        <footer class="post-footer">
          <div class="post-navigation">
            <a href="/blog/" class="back-to-blog">← Back to blog</a>

            <div v-if="prevPost || nextPost" class="post-nav-links">
              <a
                v-if="prevPost"
                :href="prevPost.url"
                class="post-nav-link prev"
              >
                <span class="nav-label">Previous</span>
                <span class="nav-title">{{ prevPost.title }}</span>
              </a>

              <a
                v-if="nextPost"
                :href="nextPost.url"
                class="post-nav-link next"
              >
                <span class="nav-label">Next</span>
                <span class="nav-title">{{ nextPost.title }}</span>
              </a>
            </div>
          </div>
        </footer>
      </article>
    </div>
  </div>
</template>

<style scoped>
.blog-post {
  padding: 48px 24px;
  max-width: 1152px;
  margin: 0 auto;
}

.container {
  max-width: 768px;
  margin: 0 auto;
}

.back-to-docs {
  display: inline-flex;
  align-items: center;
  margin-bottom: 32px;
  color: var(--vp-c-brand-1);
  text-decoration: none;
  font-weight: 500;
  font-size: 0.875rem;
  transition: color 0.2s;
}

.back-to-docs:hover {
  color: var(--vp-c-brand-2);
}

/* Header */
.post-header {
  margin-bottom: 48px;
  padding-bottom: 32px;
  border-bottom: 1px solid var(--vp-c-divider);
}

.post-date {
  display: block;
  font-size: 0.875rem;
  color: var(--vp-c-text-3);
  margin-bottom: 12px;
}

.post-title {
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1.2;
  margin: 0 0 24px;
  color: var(--vp-c-text-1);
}

.post-author-section {
  margin-top: 24px;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.author-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  flex-shrink: 0;
}

.author-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.author-name {
  font-size: 1rem;
  font-weight: 600;
  color: var(--vp-c-text-1);
}

.author-twitter {
  font-size: 0.875rem;
  color: var(--vp-c-text-3);
  text-decoration: none;
  transition: color 0.2s;
}

.author-twitter:hover {
  color: var(--vp-c-brand-1);
}

/* Content */
.post-content {
  margin-bottom: 64px;
}

.post-content :deep(.vp-doc) {
  padding: 0;
}

.post-content :deep(h2) {
  margin-top: 48px;
  margin-bottom: 16px;
  font-size: 1.75rem;
  font-weight: 600;
  line-height: 1.3;
  color: var(--vp-c-text-1);
}

.post-content :deep(h3) {
  margin-top: 32px;
  margin-bottom: 12px;
  font-size: 1.375rem;
  font-weight: 600;
  line-height: 1.4;
  color: var(--vp-c-text-1);
}

.post-content :deep(p) {
  margin: 16px 0;
  line-height: 1.7;
  color: var(--vp-c-text-2);
}

.post-content :deep(ul),
.post-content :deep(ol) {
  margin: 16px 0;
  padding-left: 1.5rem;
  line-height: 1.7;
  color: var(--vp-c-text-2);
}

.post-content :deep(li) {
  margin: 8px 0;
}

.post-content :deep(code) {
  font-size: 0.875em;
  padding: 0.25em 0.4em;
  border-radius: 4px;
  background-color: var(--vp-c-bg-soft);
  color: var(--vp-c-text-1);
}

.post-content :deep(pre) {
  margin: 24px 0;
  border-radius: 8px;
}

.post-content :deep(pre code) {
  padding: 0;
  background: transparent;
}

.post-content :deep(a) {
  color: var(--vp-c-brand-1);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.post-content :deep(a:hover) {
  color: var(--vp-c-brand-2);
}

.post-content :deep(blockquote) {
  margin: 24px 0;
  padding-left: 20px;
  border-left: 3px solid var(--vp-c-divider);
  color: var(--vp-c-text-2);
  font-style: italic;
}

.post-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 24px 0;
}

/* Footer */
.post-footer {
  margin-top: 64px;
  padding-top: 32px;
  border-top: 1px solid var(--vp-c-divider);
}

.post-navigation {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.back-to-blog {
  display: inline-flex;
  align-items: center;
  color: var(--vp-c-brand-1);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.back-to-blog:hover {
  color: var(--vp-c-brand-2);
}

.post-nav-links {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.post-nav-link {
  display: flex;
  flex-direction: column;
  padding: 16px;
  border: 1px solid var(--vp-c-divider);
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.2s;
}

.post-nav-link:hover {
  border-color: var(--vp-c-brand-1);
  background-color: var(--vp-c-bg-soft);
}

.post-nav-link.prev {
  text-align: left;
}

.post-nav-link.next {
  text-align: right;
  margin-left: auto;
}

.nav-label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--vp-c-text-3);
  margin-bottom: 4px;
}

.nav-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--vp-c-text-1);
}

/* Responsive */
@media (max-width: 768px) {
  .blog-post {
    padding: 32px 16px;
  }

  .post-title {
    font-size: 2rem;
  }

  .post-header {
    margin-bottom: 32px;
    padding-bottom: 24px;
  }

  .author-avatar {
    width: 40px;
    height: 40px;
  }

  .post-content {
    margin-bottom: 48px;
  }

  .post-content :deep(h2) {
    font-size: 1.5rem;
    margin-top: 32px;
  }

  .post-content :deep(h3) {
    font-size: 1.25rem;
    margin-top: 24px;
  }

  .post-nav-links {
    grid-template-columns: 1fr;
  }

  .post-nav-link.next {
    margin-left: 0;
  }
}
</style>

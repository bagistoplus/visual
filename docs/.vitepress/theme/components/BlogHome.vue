<script setup lang="ts">
import { data as posts } from '../blog.data';
import { useData } from 'vitepress';

const { frontmatter } = useData();
</script>

<template>
  <div class="blog-home">
    <div class="container">
      <a href="/introduction/getting-started" class="back-to-docs">← Back to Docs</a>

      <div class="header">
        <h1 class="title">{{ frontmatter.title || 'Blog' }}</h1>
        <p v-if="frontmatter.subtext" class="subtext">{{ frontmatter.subtext }}</p>
      </div>

      <div class="posts">
        <article v-for="post in posts" :key="post.url" class="post">
          <div class="post-header">
            <time class="post-date">{{ post.date.string }}</time>
            <h2 class="post-title">
              <a :href="post.url">{{ post.title }}</a>
            </h2>
          </div>

          <div v-if="post.author" class="post-meta">
            <span class="post-author">
              <img
                v-if="post.gravatar"
                :src="`https://www.gravatar.com/avatar/${post.gravatar}`"
                :alt="post.author"
                class="author-avatar"
              />
              {{ post.author }}
            </span>
            <a
              v-if="post.twitter"
              :href="`https://twitter.com/${post.twitter.replace('@', '')}`"
              target="_blank"
              rel="noopener noreferrer"
              class="post-twitter"
            >
              {{ post.twitter }}
            </a>
          </div>

          <p v-if="post.excerpt" class="post-excerpt" v-html="post.excerpt"></p>

          <a :href="post.url" class="post-link">Read more →</a>
        </article>
      </div>
    </div>
  </div>
</template>

<style scoped>
.blog-home {
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

.header {
  margin-bottom: 48px;
  text-align: center;
}

.title {
  font-size: 3rem;
  font-weight: 700;
  line-height: 1.2;
  margin: 0 0 16px;
  color: var(--vp-c-text-1);
}

.subtext {
  font-size: 1.25rem;
  color: var(--vp-c-text-2);
  margin: 0;
}

.posts {
  display: flex;
  flex-direction: column;
  gap: 48px;
}

.post {
  border-bottom: 1px solid var(--vp-c-divider);
  padding-bottom: 48px;
}

.post:last-child {
  border-bottom: none;
}

.post-header {
  margin-bottom: 12px;
}

.post-date {
  display: block;
  font-size: 0.875rem;
  color: var(--vp-c-text-3);
  margin-bottom: 8px;
}

.post-title {
  font-size: 2rem;
  font-weight: 600;
  line-height: 1.3;
  margin: 0;
}

.post-title a {
  color: var(--vp-c-text-1);
  text-decoration: none;
  transition: color 0.2s;
}

.post-title a:hover {
  color: var(--vp-c-brand-1);
}

.post-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
  font-size: 0.875rem;
  color: var(--vp-c-text-2);
}

.post-author {
  display: flex;
  align-items: center;
  gap: 8px;
}

.author-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
}

.post-twitter {
  color: var(--vp-c-text-3);
  text-decoration: none;
}

.post-twitter:hover {
  color: var(--vp-c-brand-1);
}

.post-excerpt {
  color: var(--vp-c-text-2);
  margin: 0 0 16px;
  line-height: 1.6;
}

.post-excerpt :deep(p) {
  margin: 0;
}

.post-link {
  display: inline-block;
  color: var(--vp-c-brand-1);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.post-link:hover {
  color: var(--vp-c-brand-2);
}

@media (max-width: 768px) {
  .blog-home {
    padding: 32px 16px;
  }

  .title {
    font-size: 2rem;
  }

  .subtext {
    font-size: 1rem;
  }

  .post-title {
    font-size: 1.5rem;
  }

  .posts {
    gap: 32px;
  }

  .post {
    padding-bottom: 32px;
  }
}
</style>

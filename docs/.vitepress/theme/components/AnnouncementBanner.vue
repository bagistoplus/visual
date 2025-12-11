<script setup>
import { ref, onMounted } from 'vue';

const STORAGE_KEY = 'vitepress-announcement-dismissed';
const isVisible = ref(true);

onMounted(() => {
  const dismissed = localStorage.getItem(STORAGE_KEY);
  if (dismissed === 'true') {
    isVisible.value = false;
    document.documentElement.classList.add('banner-dismissed');
  }
});

function dismiss() {
  isVisible.value = false;
  document.documentElement.classList.add('banner-dismissed');
  localStorage.setItem(STORAGE_KEY, 'true');
}
</script>

<template>
  <div v-if="isVisible" class="banner">
    <a href="/blog/announcing-v2-blocks-system" class="banner-link">
      <span class="banner-icon">🎉</span>
      <span class="banner-text">
        <strong>Bagisto Visual v2 is here!</strong> Introducing a modular nested block system
        <span class="banner-cta">Learn more →</span>
      </span>
    </a>
    <button @click="dismiss" class="banner-close" aria-label="Dismiss announcement">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
        <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
    </button>
  </div>
</template>

<style>
html:not(.banner-dismissed) {
  --vp-layout-top-height: 30px;
}
</style>

<style scoped>
.banner {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 10;
  height: var(--vp-layout-top-height);
  padding: 0 24px;
  background: linear-gradient(120deg, #bd34fe 30%, #41d1ff);
  color: white;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: center;
  align-items: center;
}

.banner-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  text-decoration: none;
  color: white;
  transition: opacity 0.2s;
}

.banner-link:hover {
  opacity: 0.9;
}

.banner-icon {
  font-size: 16px;
  flex-shrink: 0;
}

.banner-text {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  line-height: 1;
}

.banner-text strong {
  font-weight: 600;
}

.banner-cta {
  margin-left: 8px;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 500;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  transition: background 0.2s;
  flex-shrink: 0;
}

.banner-link:hover .banner-cta {
  background: rgba(255, 255, 255, 0.3);
}

.banner-close {
  position: absolute;
  right: 24px;
  padding: 6px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  border-radius: 4px;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.banner-close:hover {
  background: rgba(255, 255, 255, 0.15);
}

@media (max-width: 768px) {
  .banner {
    padding: 0 16px;
  }

  .banner-close {
    right: 16px;
  }

  .banner-text {
    font-size: 12px;
  }

  .banner-icon {
    font-size: 14px;
  }
}

@media (max-width: 640px) {
  .banner-text {
    gap: 6px;
  }

  .banner-cta {
    display: none;
  }
}
</style>

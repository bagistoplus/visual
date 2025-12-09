<script setup>
import { ref, onMounted } from 'vue';
import EditorMockup from './EditorMockup.vue';
import { VPButton } from 'vitepress/theme';

const showTitle = ref(false);
const showTagline = ref(false);
const showButtons = ref(false);
const showMockup = ref(false);

onMounted(() => {
  setTimeout(() => { showTitle.value = true; }, 0);
  setTimeout(() => { showTagline.value = true; }, 100);
  setTimeout(() => { showButtons.value = true; }, 200);
  setTimeout(() => { showMockup.value = true; }, 0);
});
</script>

<template>
  <section class="hero-section">
    <!-- Background Glows (Vite style) -->
    <div class="glow-top-right">
      <div class="glow-orb glow-cyan-purple"></div>
    </div>
    <div class="glow-bottom-left">
      <div class="glow-orb glow-blue"></div>
    </div>

    <div class="hero-container">
      <!-- Left Column: Text -->
      <div class="hero-text">
        <Transition name="fade-up">
          <h1
            v-if="showTitle"
            class="hero-title"
          >
            <span class="gradient-text">Bagisto Visual</span>
            <span class="title-white">Storefront Builder</span>
          </h1>
        </Transition>

        <Transition name="fade-up">
          <p
            v-if="showTagline"
            class="hero-tagline"
          >
            Next Generation Frontend Tooling for Bagisto.
            <br class="line-break-responsive" />
            Design, customize, and deploy bagisto storefronts without writing code.
          </p>
        </Transition>

        <Transition name="fade-up">
          <div
            v-if="showButtons"
            class="hero-actions"
          >
            <VPButton
              theme="brand"
              size="big"
              href="/introduction/getting-started"
              text="Get Started"
            />
            <VPButton
              theme="alt"
              size="big"
              href="/introduction/what-is-bagisto-visual"
              text="Why Visual?"
            />
            <VPButton
              theme="alt"
              size="big"
              href="https://github.com/bagistoplus/visual"
              text="View on GitHub"
            />
          </div>
        </Transition>
      </div>

      <!-- Right Column: Visual/Image -->
      <div class="hero-visual">
        <Transition name="scale-fade">
          <div
            v-if="showMockup"
            class="mockup-wrapper"
          >
            <EditorMockup />
          </div>
        </Transition>
      </div>
    </div>
  </section>
</template>

<style scoped>
.hero-section {
  position: relative;
  padding: 128px 16px 80px;
  overflow: hidden;
  /* Use VitePress native background */
}

/* Background Glows */
.glow-top-right {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 0;
  opacity: 0.5;
  transform: translate(50%, -25%);
  pointer-events: none;
}

.glow-bottom-left {
  position: absolute;
  bottom: 0;
  left: 0;
  z-index: 0;
  opacity: 0.3;
  transform: translate(-33.33%, 25%);
  pointer-events: none;
}

.glow-orb {
  border-radius: 50%;
}

.glow-cyan-purple {
  width: 600px;
  height: 600px;
  background: linear-gradient(135deg, rgba(6, 182, 212, 0.2) 0%, rgba(168, 85, 247, 0.3) 100%);
  filter: blur(100px);
}

.glow-blue {
  width: 500px;
  height: 500px;
  background: rgba(59, 130, 246, 0.2);
  filter: blur(80px);
}

.hero-container {
  max-width: 1280px;
  margin: 0 auto;
  display: flex;
  flex-direction: column-reverse;
  align-items: center;
  gap: 48px;
  position: relative;
  z-index: 1;
}

.hero-text {
  flex: 1;
  width: 100%;
  text-align: center;
  z-index: 10;
}

.hero-title {
  font-size: 48px;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -0.025em;
  margin: 0 0 24px;
}

.hero-title span {
  display: block;
}

.gradient-text {
  background: linear-gradient(135deg, #41d1ff 0%, #bd34fe 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.title-white {
  color: var(--vp-c-text-1);
}

.hero-tagline {
  font-size: 20px;
  line-height: 1.6;
  color: var(--vp-c-text-2);
  margin: 0 0 40px;
  max-width: 672px;
  margin-left: auto;
  margin-right: auto;
}

.line-break-responsive {
  display: none;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 16px;
}

.hero-actions :deep(.VPButton) {
  text-decoration: none;
}

.hero-visual {
  flex: 1;
  width: 100%;
  display: flex;
  justify-content: center;
  position: relative;
}

.mockup-wrapper {
  width: 100%;
  max-width: 512px;
}

/* Animations */
.fade-up-enter-active {
  transition: all 0.5s ease;
}

.fade-up-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-up-enter-to {
  opacity: 1;
  transform: translateY(0);
}

.scale-fade-enter-active {
  transition: all 0.8s ease;
}

.scale-fade-enter-from {
  opacity: 0;
  transform: scale(0.8);
}

.scale-fade-enter-to {
  opacity: 1;
  transform: scale(1);
}

/* Responsive */
@media (min-width: 640px) {
  .hero-section {
    padding: 160px 24px 96px;
  }

  .line-break-responsive {
    display: inline;
  }

  .hero-title {
    font-size: 56px;
  }
}

@media (min-width: 1024px) {
  .hero-container {
    flex-direction: row;
    gap: 32px;
  }

  .hero-text {
    width: 50%;
    text-align: left;
  }

  .hero-title {
    font-size: 60px;
  }

  .hero-tagline {
    margin-left: 0;
    margin-right: 0;
  }

  .hero-actions {
    justify-content: flex-start;
  }

  .hero-visual {
    width: 50%;
    justify-content: flex-end;
  }
}

@media (max-width: 639px) {
  .hero-actions {
    flex-direction: column;
    width: 100%;
  }

  .hero-visual {
    display: none;
  }
}
</style>
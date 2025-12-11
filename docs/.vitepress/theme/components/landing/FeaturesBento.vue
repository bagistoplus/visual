<script setup>
const features = [
  {
    id: 'framework',
    icon: '🎨',
    title: 'Developer Framework',
    description: 'A robust toolkit for developers to build highly customizable themes that expose controls to shop owners.'
  },
  {
    id: 'editor',
    icon: '🖱️',
    title: 'Shop Owner Visual Editor',
    description: 'Empower store owners to customize layouts and content with an intuitive drag & drop interface.'
  },
  {
    id: 'speed',
    icon: '⚡',
    title: 'Instant Live Editing',
    description: 'See changes in real-time. No compilation delays for shop owners when tweaking their storefront.'
  },
  {
    id: 'responsive',
    icon: '📱',
    title: 'Mobile Optimized',
    description: 'Themes built on the framework adapt perfectly to any device, with visual breakpoint controls.'
  }
];
</script>

<template>
  <section class="landing-section features-section">
    <div class="container-lg">
      <div class="section-intro text-center">
        <h2 class="section-title">
          A complete toolkit for <br />
          <span class="gradient-text">Developers & Shop Owners</span>
        </h2>
        <p class="section-subtitle">
          Bridging the gap between code and design. Developers build the structure, shop owners control the look.
        </p>
      </div>

      <div class="features-grid">
        <div
          v-for="(feature, index) in features"
          :key="feature.id"
          :class="['feature-card', { 'span-wide': index === 0 || index === 3 }]"
        >
          <div class="feature-content">
            <div class="feature-icon">
              <span>{{ feature.icon }}</span>
            </div>

            <h3 class="feature-title">{{ feature.title }}</h3>

            <p class="feature-description">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.features-section {
  padding: 96px 24px;
}

.section-intro {
  margin-bottom: 64px;
}

.section-title {
  font-size: 36px;
  font-weight: 700;
  margin-bottom: 16px;
  color: var(--vp-c-text-1);
  line-height: 1.2;
}

.section-subtitle {
  font-size: 16px;
  color: var(--vp-c-text-2);
  max-width: 672px;
  margin: 0 auto;
  line-height: 1.6;
}

.features-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
}

.feature-card {
  position: relative;
  background: #1e1e20;
  padding: 32px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  overflow: hidden;
  transition: all 0.3s ease;
}

.feature-card:hover {
  border-color: rgba(255, 255, 255, 0.1);
}

.feature-content {
  position: relative;
}

.feature-icon {
  width: 48px;
  height: 48px;
  background: #252529;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 24px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  font-size: 24px;
}

.feature-card:hover .feature-icon {
  transform: scale(1.1);
  border-color: rgba(59, 130, 246, 0.3);
  box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
}

.feature-title {
  font-size: 20px;
  font-weight: 600;
  color: #e5e5e5;
  margin: 0 0 12px;
  transition: color 0.3s ease;
}

.feature-card:hover .feature-title {
  color: #ffffff;
}

.feature-description {
  font-size: 16px;
  line-height: 1.6;
  color: #a1a1aa;
  margin: 0;
  transition: color 0.3s ease;
}

.feature-card:hover .feature-description {
  color: #d4d4d8;
}

/* Responsive */
@media (min-width: 768px) {
  .section-title {
    font-size: 48px;
  }

  .section-subtitle {
    font-size: 18px;
  }

  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .features-grid {
    grid-template-columns: repeat(3, 1fr);
  }

  .span-wide {
    grid-column: span 2;
  }
}
</style>

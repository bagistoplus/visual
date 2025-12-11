<script setup>
const sponsors = [
  { name: 'Laravel', logo: '🔴', color: '#FF2D20' },
  { name: 'Bagisto', logo: '🛍️', color: '#3b82f6' },
  { name: 'Tailwind CSS', logo: '🎨', color: '#06b6d4' },
  { name: 'Vue.js', logo: '💚', color: '#42b883' },
  { name: 'Stripe', logo: '💳', color: '#635bff' },
  { name: 'PayPal', logo: '💰', color: '#0070ba' },
];
</script>

<template>
  <section class="landing-section sponsors-section">
    <div class="container-lg">
      <div class="section-intro text-center">
        <h3 class="sponsors-title">Built with industry-leading technologies</h3>
      </div>

      <div class="sponsors-grid">
        <div
          v-for="(sponsor, index) in sponsors"
          :key="index"
          class="sponsor-card"
          :style="{ '--sponsor-color': sponsor.color }"
        >
          <div class="sponsor-logo">{{ sponsor.logo }}</div>
          <div class="sponsor-name">{{ sponsor.name }}</div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.sponsors-section {
  /* Use VitePress native background */
  padding-top: 80px;
  padding-bottom: 80px;
}

.sponsors-title {
  font-size: 20px;
  font-weight: 600;
  color: var(--landing-text-muted);
  margin: 0 0 48px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.sponsors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 24px;
  max-width: 900px;
  margin: 0 auto;
}

.sponsor-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 32px 24px;
  background: var(--landing-surface);
  border: 1px solid var(--landing-border);
  border-radius: 12px;
  transition: all 0.3s ease;
  filter: grayscale(1);
  opacity: 0.6;
}

.sponsor-card:hover {
  filter: grayscale(0);
  opacity: 1;
  border-color: var(--sponsor-color);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  transform: translateY(-4px);
}

.sponsor-logo {
  font-size: 48px;
  transition: transform 0.3s ease;
}

.sponsor-card:hover .sponsor-logo {
  transform: scale(1.1);
}

.sponsor-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--landing-text);
  transition: color 0.3s ease;
}

.sponsor-card:hover .sponsor-name {
  color: var(--sponsor-color);
}

/* Responsive */
@media (max-width: 768px) {
  .sponsors-section {
    padding-top: 60px;
    padding-bottom: 60px;
  }

  .sponsors-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }

  .sponsor-card {
    padding: 24px 16px;
  }

  .sponsor-logo {
    font-size: 36px;
  }

  .sponsors-title {
    font-size: 16px;
    margin-bottom: 32px;
  }
}
</style>

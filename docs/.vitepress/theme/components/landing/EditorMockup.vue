<script setup>
import { ref, onMounted } from 'vue';

const isVisible = ref(false);
const cursorX = ref(0);
const cursorY = ref(0);

onMounted(() => {
  setTimeout(() => {
    isVisible.value = true;
  }, 300);

  // Animate cursor movement
  let startTime = Date.now();
  const duration = 4000;

  const animate = () => {
    const elapsed = Date.now() - startTime;
    const progress = (elapsed % duration) / duration;

    // Keyframes for x: [0, 60, 60, 0]
    if (progress < 0.25) {
      cursorX.value = 60 * (progress / 0.25);
    } else if (progress < 0.75) {
      cursorX.value = 60;
    } else {
      cursorX.value = 60 * (1 - (progress - 0.75) / 0.25);
    }

    // Keyframes for y: [0, 30, 0, 0]
    if (progress < 0.25) {
      cursorY.value = 30 * (progress / 0.25);
    } else if (progress < 0.5) {
      cursorY.value = 30 * (1 - (progress - 0.25) / 0.25);
    } else {
      cursorY.value = 0;
    }

    requestAnimationFrame(animate);
  };
  animate();
});
</script>

<template>
  <Transition name="fade-scale">
    <div v-if="isVisible" class="editor-mockup-container">
      <!-- Glow decoration -->
      <div class="mockup-glow"></div>

      <!-- Editor Window -->
      <div class="editor-window">
        <!-- Window Header -->
        <div class="window-header">
          <div class="window-controls">
            <span class="window-dot dot-red"></span>
            <span class="window-dot dot-yellow"></span>
            <span class="window-dot dot-green"></span>
          </div>
          <div class="window-title">Visual Editor</div>
        </div>

        <!-- Editor Content -->
        <div class="editor-body">
          <!-- Left Sidebar -->
          <div class="editor-sidebar">
            <div class="sidebar-item active">
              <div class="sidebar-icon">📦</div>
            </div>
            <div class="sidebar-item">
              <div class="sidebar-icon">🎨</div>
            </div>
            <div class="sidebar-item">
              <div class="sidebar-icon">⚙️</div>
            </div>
            <div class="sidebar-item">
              <div class="sidebar-icon">👁️</div>
            </div>
          </div>

          <!-- Canvas Area -->
          <div class="editor-canvas">
            <!-- Canvas Header -->
            <div class="canvas-header">
              <div class="breadcrumb">Home › Hero Section</div>
            </div>

            <!-- Canvas Body -->
            <div class="canvas-body">
              <!-- Hero Section Placeholder -->
              <div class="section-placeholder hero-placeholder">
                <span class="placeholder-label">Hero Section</span>
                <div class="placeholder-actions">
                  <span class="action-dot"></span>
                  <span class="action-dot"></span>
                  <span class="action-dot"></span>
                </div>
              </div>

              <!-- Feature Grid Placeholder -->
              <div class="grid-placeholder">
                <div class="grid-item"></div>
                <div class="grid-item"></div>
                <div class="grid-item"></div>
              </div>

              <!-- Floating Cursor -->
              <div
                class="floating-cursor"
                :style="{ transform: `translate(${cursorX}px, ${cursorY}px)` }"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"></path>
                  <path d="M13 13l6 6"></path>
                </svg>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.editor-mockup-container {
  position: relative;
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
}

.mockup-glow {
  position: absolute;
  inset: -2px;
  background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 50%, #a855f7 100%);
  border-radius: 20px;
  filter: blur(24px);
  opacity: 0.4;
  z-index: -1;
}

.editor-window {
  background: #1e1e20;
  border: 1px solid #333;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
  transform: perspective(1200px) rotateY(-2deg) rotateX(2deg);
  transition: transform 0.3s ease;
}

.editor-window:hover {
  transform: perspective(1200px) rotateY(0deg) rotateX(0deg);
}

.window-header {
  height: 44px;
  background: #242424;
  border-bottom: 1px solid #333;
  display: flex;
  align-items: center;
  padding: 0 16px;
  gap: 12px;
}

.window-controls {
  display: flex;
  gap: 8px;
}

.window-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.dot-red {
  background: #ff5f56;
}

.dot-yellow {
  background: #ffbd2e;
}

.dot-green {
  background: #27c93f;
}

.window-title {
  font-size: 13px;
  color: #a1a1aa;
  font-weight: 500;
}

.editor-body {
  display: grid;
  grid-template-columns: 60px 1fr;
  height: 350px;
}

.editor-sidebar {
  background: #242424;
  border-right: 1px solid #333;
  padding: 16px 8px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.sidebar-item {
  padding: 12px 8px;
  border-radius: 8px;
  transition: background 0.2s;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar-item:hover,
.sidebar-item.active {
  background: rgba(59, 130, 246, 0.15);
}

.sidebar-icon {
  font-size: 24px;
}

.editor-canvas {
  display: flex;
  flex-direction: column;
  background: #0a0a0a;
}

.canvas-header {
  padding: 12px 20px;
  border-bottom: 1px solid #333;
  background: #1e1e20;
}

.breadcrumb {
  font-size: 12px;
  color: #a1a1aa;
  font-family: monospace;
}

.canvas-body {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
  position: relative;
}

.section-placeholder {
  background: #1e1e20;
  border: 2px dashed #555;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 16px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.hero-placeholder {
  height: 140px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.section-placeholder:hover {
  border-color: #646cff;
  background: rgba(100, 108, 255, 0.05);
}

.placeholder-label {
  font-size: 14px;
  color: #71717a;
  font-weight: 500;
  transition: color 0.3s;
}

.section-placeholder:hover .placeholder-label {
  color: #646cff;
}

.placeholder-actions {
  display: flex;
  gap: 4px;
}

.action-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #71717a;
}

.grid-placeholder {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.grid-item {
  height: 100px;
  background: #1e1e20;
  border-radius: 8px;
  border: 1px solid #333;
}

.floating-cursor {
  position: absolute;
  top: 50%;
  left: 33%;
  color: #646cff;
  width: 20px;
  height: 20px;
  filter: drop-shadow(0 2px 8px rgba(100, 108, 255, 0.6));
  pointer-events: none;
  z-index: 10;
}

/* Animations */
.fade-scale-enter-active {
  transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-scale-enter-from {
  opacity: 0;
  transform: perspective(1200px) rotateY(-10deg) rotateX(10deg) scale(0.9);
}

.fade-scale-enter-to {
  opacity: 1;
  transform: perspective(1200px) rotateY(-2deg) rotateX(2deg) scale(1);
}

/* Responsive */
@media (max-width: 1024px) {
  .editor-body {
    grid-template-columns: 50px 1fr;
  }
}

@media (max-width: 768px) {
  .editor-body {
    height: 300px;
  }

  .grid-placeholder {
    grid-template-columns: 1fr;
  }

  .sidebar-icon {
    font-size: 20px;
  }
}
</style>

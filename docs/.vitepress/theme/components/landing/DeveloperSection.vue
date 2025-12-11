<script setup>
import { ref, onMounted } from 'vue';
import { codeToHtml } from 'shiki';

const activeTab = ref('block');
const highlightedCode = ref({ block: '', view: '' });
const isLoading = ref(true);

const codeExamples = {
  block: `<?php

namespace App\\Visual\\Blocks;

use BagistoPlus\\Visual\\Blocks\\SimpleBlock;
use BagistoPlus\\Visual\\Settings\\{Text, Select};
use App\\Visual\\Blocks\\{Button,Image,Heading,Text as TextBlock};

class HeroBlock extends SimpleBlock
{
    protected static string $view = 'shop::blocks.hero';

    protected static array $accepts = [
        Button::class,
        Image::class,
        Heading::class,
        TextBlock::class,
    ];

    public static function settings(): array
    {
        return [
            Text::make('title', 'Title')
                ->default('Welcome'),
            Select::make('layout', 'Layout')
                ->options([
                    'centered' => 'Centered',
                    'split' => 'Split',
                ])
                ->default('centered'),
        ];
    }
}`,
  view: `<section {{ $block->editor_attributes }} class="hero hero--{{ $block->layout }}">
    <div class="container">
        <h1>{{ $block->title }}</h1>

        @if($block->hasChildren())
            <div class="hero__actions">
              @children
            </div>
        @endif
    </div>
</section>`
};

onMounted(async () => {
  try {
    highlightedCode.value.block = await codeToHtml(codeExamples.block, {
      lang: 'php',
      theme: 'material-theme-palenight'
    });

    highlightedCode.value.view = await codeToHtml(codeExamples.view, {
      lang: 'blade',
      theme: 'material-theme-palenight'
    });

    isLoading.value = false;
  } catch (error) {
    console.error('Failed to highlight code:', error);
    isLoading.value = false;
  }
});

const features = [
  "Define editable regions in Blade",
  "Full access to Bagisto service container",
  "Shop owners can't break the layout",
  "Git-friendly theme structure"
];
</script>

<template>
  <section class="developer-section">
    <div class="developer-container">
      <div class="developer-grid">
        <!-- Left: Content -->
        <div class="developer-content">
          <h2 class="section-title">
            Developer First Framework, <br />
            <span class="text-muted">Shop Owner Friendly Results.</span>
          </h2>

          <p class="section-description">
            Bagisto Visual isn't just a visual builder; it's a theme framework. Define your components in Blade, exposing only what's necessary for the visual editor.
          </p>

          <ul class="feature-checklist">
            <li
              v-for="(feature, i) in features"
              :key="i"
              class="checklist-item"
            >
              <div class="check-icon">
                <span>✓</span>
              </div>
              <span>{{ feature }}</span>
            </li>
          </ul>
        </div>

        <!-- Right: Code Block -->
        <div class="code-showcase">
          <div class="code-window">
            <!-- Window Controls with Tabs -->
            <div class="code-header">
              <div class="window-controls">
                <span class="dot dot-red"></span>
                <span class="dot dot-yellow"></span>
                <span class="dot dot-green"></span>
              </div>

              <div class="header-tabs">
                <button
                  :class="['tab-filename', { active: activeTab === 'block' }]"
                  @click="activeTab = 'block'"
                >
                  HeroBlock.php
                </button>
                <button
                  :class="['tab-filename', { active: activeTab === 'view' }]"
                  @click="activeTab = 'view'"
                >
                  hero.blade.php
                </button>
              </div>

              <div class="spacer"></div>
            </div>

            <!-- Code Area -->
            <div class="code-body">
              <div
                v-if="isLoading"
                class="code-loading"
              >Loading...</div>
              <div
                v-else
                v-html="highlightedCode[activeTab]"
                class="highlighted-code"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.developer-section {
  position: relative;
  padding: 96px 24px;
  overflow: hidden;
}


.developer-container {
  max-width: 1280px;
  margin: 0 auto;
  position: relative;
}

.developer-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 64px;
  align-items: center;
}

.developer-content {
  max-width: 600px;
}

.section-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 24px;
  color: var(--vp-c-text-1);
  line-height: 1.2;
}

.text-muted {
  color: #6b7280;
}

.section-description {
  font-size: 18px;
  line-height: 1.7;
  color: var(--vp-c-text-2);
  margin-bottom: 32px;
}

.feature-checklist {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.checklist-item {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #d1d5db;
}

.check-icon {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(34, 197, 94, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.check-icon span {
  color: #22c55e;
  font-size: 12px;
  font-weight: 700;
}

.code-showcase {
  position: relative;
  width: 100%;
  max-width: 600px;
}

.header-tabs {
  display: flex;
  gap: 0;
  flex: 1;
  justify-content: center;
}

.tab-filename {
  padding: 0 16px;
  background: transparent;
  border: none;
  color: #6b7280;
  font-size: 12px;
  font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
  cursor: pointer;
  transition: all 0.2s;
  height: 100%;
  display: flex;
  align-items: center;
}

.tab-filename:hover {
  color: #9ca3af;
  background: rgba(255, 255, 255, 0.05);
}

.tab-filename.active {
  color: #e5e7eb;
  background: #1a1b26;
}

.code-window {
  position: relative;
  background: #1a1b26;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
  overflow: hidden;
  font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
  font-size: 14px;
}

.code-header {
  height: 40px;
  background: #16161e;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  align-items: center;
  padding: 0 16px;
  justify-content: space-between;
}

.window-controls {
  display: flex;
  gap: 8px;
}

.dot {
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


.spacer {
  width: 40px;
}

.code-body {
  padding: 24px;
  overflow-x: auto;
  height: 400px;
  overflow-y: auto;
}

.code-loading {
  color: #6b7280;
  text-align: center;
  padding: 40px;
}

.highlighted-code {
  margin: 0;
}

.highlighted-code :deep(pre) {
  margin: 0;
  padding: 0;
  background: transparent !important;
  font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
  font-size: 14px;
  line-height: 1.5;
}

.highlighted-code :deep(code) {
  display: block;
  background: transparent !important;
}

/* Responsive */
@media (min-width: 768px) {
  .section-title {
    font-size: 32px;
  }
}

@media (min-width: 1024px) {
  .developer-grid {
    grid-template-columns: 1fr 1fr;
    gap: 64px;
  }
}

@media (max-width: 768px) {
  .developer-section {
    padding: 64px 16px;
  }

  .code-body {
    padding: 16px;
  }

  .code-content {
    font-size: 12px;
  }
}
</style>
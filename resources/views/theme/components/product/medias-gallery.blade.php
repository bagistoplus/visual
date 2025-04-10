@props(['medias' => []])

<div
  x-data
  x-media-gallery="{ medias: @js($medias) }"
  {{ $attributes->merge(['class' => 'flex flex-col md:flex-row gap-4']) }}
>

  <!-- Scroll Up Button -->
  <button x-media-gallery:scroll-up-trigger class="absolute left-7 top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-black/50 text-white">
    <x-lucide-chevron-up class="h-4 w-4" />
  </button>

  <!-- Scroll Down Button -->
  <button x-media-gallery:scroll-down-trigger class="absolute bottom-2 left-7 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-black/50 text-white">
    <x-lucide-chevron-down class="h-4 w-4" />
  </button>

  <div x-media-gallery:thumbs class="order-last flex flex-row gap-4 overflow-hidden md:absolute md:inset-y-0 md:order-first md:flex-col">
    <template x-for="(media, index) in medias" :key="index">
      <button
        x-media-gallery:thumb="index"
        class="aspect-square w-20 flex-shrink-0 overflow-hidden rounded-lg bg-neutral-100 focus:outline-none"
        x-bind:class="{ 'border-2 border-primary': $thumb.isSelected }"
      >
        <template x-if="media.type !== 'videos'">
          <img
            x-bind:src="media.small_image_url"
            x-bind:alt="media.small_image_url"
            class="h-full w-full object-cover object-center"
          >
        </template>
        <template x-if="media.type === 'videos'">
          <div class="relative h-full">
            <x-lucide-play-circle class="absolute left-1/2 top-1/2 h-8 w-8 -translate-x-1/2 -translate-y-1/2 transform text-gray-400/50" />
            <video
              muted
              height="100%"
              x-bind:alt="media.video_url"
            >
              <source x-bind:src="media.video_url" />
            </video>
          </div>
        </template>
      </button>
    </template>
  </div>

  <div class="flex aspect-square h-auto flex-1 items-center justify-center overflow-hidden bg-gray-100 md:ml-24">
    <template x-if="selectedMedia.type !== 'videos'">
      <img
        :src="selectedMedia.large_image_url"
        alt="Rose Quartz Face Serum"
        class="aspect-square h-full w-full rounded-lg object-cover object-center"
      >
    </template>
    <template x-if="selectedMedia.type === 'videos'">
      <video
        controls
        autoplay
        :alt="selectedMedia.video_url"
        class="h-full"
      >
        <source :src="selectedMedia.video_url" />
      </video>
    </template>
  </div>
</div>

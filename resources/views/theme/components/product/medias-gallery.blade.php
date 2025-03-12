@props(['medias' => []])

@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', function() {
      Alpine.data('VisualMediasGallery', () => ({
        isOverflowingAbove: false,
        isOverflowingBelow: false,
        container: null,
        medias: @json($medias),
        defaultMedias: [],
        selectedIndex: 0,

        get selectedMedia() {
          return this.medias[this.selectedIndex];
        },

        init() {
          this.container = this.$root.querySelector('.images');
          this.defaultMedias = [...this.medias];

          this.$nextTick(() => {
            this.checkOverflow();
          });
        },

        checkOverflow() {
          // Check if there's hidden content above or below
          this.isOverflowingAbove = this.container.scrollTop > 0;
          this.isOverflowingBelow = this.container.scrollTop + this.container.clientHeight < this.container
            .scrollHeight;
        },

        scroll(direction) {
          const scrollAmount = 100; // Adjust scroll step if needed

          // Scroll up or down
          if (direction === "up") {
            this.container.scrollBy({
              top: -scrollAmount,
              behavior: "smooth"
            });
          } else if (direction === "down") {
            this.container.scrollBy({
              top: scrollAmount,
              behavior: "smooth"
            });
          }
        },

        onMediasChanged(event) {
          if (event.detail.images.length > 0 || event.detail.videos.length > 0) {
            this.medias = [...event.detail.images, ...event.detail.videos];
          } else {
            this.medias = [...this.defaultMedias]
          }

          this.selectedIndex = 0;
        }
      }))
    })
  </script>
@endpushOnce

<div
  x-data="VisualMediasGallery"
  x-on:variant-medias-change.window="onMediasChanged"
  {{ $attributes->merge(['class' => 'flex flex-col md:flex-row gap-4']) }}
>

  <!-- Scroll Up Button -->
  <button
    x-show="isOverflowingAbove"
    class="absolute left-7 top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-black/50 text-white"
    @click="scroll('up')"
  >
    <x-lucide-chevron-up class="h-4 w-4" />
  </button>

  <!-- Scroll Down Button -->
  <button
    x-show="isOverflowingBelow"
    class="absolute bottom-2 left-7 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-black/50 text-white"
    @click="scroll('down')"
  >
    <x-lucide-chevron-down class="h-4 w-4" />
  </button>

  <div x-on:scroll="checkOverflow"
    class="images order-last flex flex-row gap-4 overflow-hidden md:absolute md:inset-y-0 md:order-first md:flex-col"
  >
    <template x-for="(media, index) in medias ">
      <button
        class="aspect-square w-20 flex-shrink-0 overflow-hidden rounded-lg bg-neutral-100"
        x-bind:class="{ 'border-2 border-primary': selectedIndex === index }"
        x-on:click="selectedIndex = index"
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
            <x-lucide-play-circle
              class="absolute left-1/2 top-1/2 h-8 w-8 -translate-x-1/2 -translate-y-1/2 transform text-gray-400/50"
            />
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

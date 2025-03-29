@props(['categories'])

@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('VisualNavigation', () => ({
        dropdownOpen: false,
        activeItem: '',
        hideDelay: 200,
        hideTimer: null,

        startHideTimer() {
          let self = this;

          this.hideTimer = setTimeout(() => {
            self.closeDropdown();
          }, this.hideDelay);
        },

        openDropdown(triggerEl, id) {
          this.dropdownOpen = true;

          this.$nextTick(() => {
            this.positionDropdown(triggerEl);
          });

          this.activeItem = id;
        },

        positionDropdown(menuItem) {
          this.cancelHideTimer();

          requestAnimationFrame(() => {
            const triggerRect = menuItem.getBoundingClientRect();
            const dropdown = this.$refs.menuDropdown;

            const dropdownWidth = dropdown.offsetWidth;
            const triggerCenter = triggerRect.left + triggerRect.width / 2;
            let left = triggerCenter - dropdownWidth / 2;

            // If dropdown is offscreen to the left, clamp it
            if (left < 100) left = 100; // Add some gutter

            // If dropdown is offscreen to the right, clamp it
            const maxLeft = window.innerWidth - dropdownWidth - 16;
            if (left > maxLeft) left = maxLeft;

            dropdown.style.left = `${left}px`;
          });
        },

        cancelHideTimer() {
          clearTimeout(this.hideTimer);
        },

        closeDropdown() {
          this.dropdownOpen = false;
          this.activeItem = null;
        }
      }));
    });
  </script>
@endpushOnce

@php
  $itemClass = 'group inline-flex h-10 w-max items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors  hover:text-neutral-900 focus:outline-none';
@endphp
<div x-data="VisualNavigation" {{ $attributes->merge(['class' => 'class="relative z-10  h-full flex items-center']) }}>
  <div class="relative">
    <ul class="group flex flex-1 list-none items-center justify-center space-x-2 rounded-md p-1.5 text-neutral-700">
      @foreach ($categories as $category)
        <li>
          @if ($category->children->isEmpty())
            <a href="{{ $category->url }}" class="{{ $itemClass }} hover:bg-neutral-100">
              {{ $category->name }}
            </a>
          @else
            <a
              href="{{ $category->url }}"
              class="{{ $itemClass }}"
              aria-haspopup="true"
              x-bind:aria-expanded="dropdownOpen && activeItem === {{ $category->id }}"
              x-on:mouseover="openDropdown($el, {{ $category->id }})"
              x-on:mouseleave="startHideTimer()"
              x-bind:class="{ 'bg-neutral-100': activeItem == {{ $category->id }}, 'hover:bg-neutral-100': activeItem!='{{ $category['id'] }}' }"
            >
              {{ $category->name }}
            </a>
          @endif
        </li>
      @endforeach
    </ul>
  </div>

  <div
    x-ref="menuDropdown"
    x-show="dropdownOpen"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90"
    x-on:mouseover="cancelHideTimer()"
    x-on:mouseleave="startHideTimer()"
    class="absolute top-full pt-1 duration-200 ease-out"
    x-cloak
  >
    <div class="bg-background flex h-auto w-auto justify-center overflow-hidden rounded-md border border-neutral-200/70 shadow-sm">
      @foreach ($categories as $category)
        @if ($category->children->isNotEmpty())
          <div x-show="activeItem === {{ $category->id }}" class="flex w-full max-w-3xl items-stretch justify-center gap-x-3 p-4">
            @if ($category->logo_url || $category->banner_url)
              <div class="relative flex h-full min-h-64 w-40 flex-shrink-0 items-end overflow-hidden rounded bg-cover bg-center bg-no-repeat p-4"
                style="background-image: url({{ $category->logo_url ?? $category->banner_url }})"
              >
                <div class="absolute inset-0 bg-neutral-900/65"></div>
                <div class="relative space-y-1.5 text-neutral-50">
                  <span class="block text-lg font-bold">{{ $category->name }}</span>
                  <span class="block text-sm opacity-90">{!! $category->description !!}</span>
                </div>
              </div>
            @endif

            <div class="flex-1">
              <div class="flex flex-wrap gap-4">
                @foreach ($category->children as $subCategory)
                  <div class="w-64 flex-none">
                    <a
                      href="{{ $subCategory->url }}"
                      x-on:click="closeDropdown()"
                      class="block rounded px-3.5 py-3 text-sm hover:bg-neutral-100"
                    >
                      <span class="mb-1 block font-medium text-neutral-900">
                        {{ $subCategory->name }}
                      </span>
                      @if ($subCategory->description)
                        <span class="block truncate leading-5 text-neutral-400">
                          {!! $subCategory->description !!}
                        </span>
                      @endif
                    </a>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endif
      @endforeach
    </div>
  </div>
</div>

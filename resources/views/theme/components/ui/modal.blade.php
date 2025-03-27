@props([
    'title' => null,
    'name' => 'modal-' . uniqid(),
])

<div
  x-data="{ modalOpen: false, name: '{{ $name }}' }"
  data-modal-name="{{ $name }}"
  x-on:keydown.escape.window="modalOpen = false"
  x-on:show-modal.window="if ($event.detail === name) modalOpen = true"
  x-on:hide-modal.window="if ($event.detail === name) modalOpen = false"
  class="relative z-50 h-auto w-auto"
>
  @isset($trigger)
    {{ $trigger }}
  @endisset

  <template x-teleport="body">
    <div
      x-cloak
      x-show="modalOpen"
      class="fixed left-0 top-0 z-[99] flex h-screen w-screen items-center justify-center"
    >
      <!-- Backdrop -->
      <div
        x-show="modalOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 h-full w-full bg-black bg-opacity-40"
        x-on:click="modalOpen = false"
      >
      </div>

      <!-- Modal Container -->
      <div
        x-show="modalOpen"
        x-trap.inert.noscroll="modalOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-auto min-w-full bg-white px-7 py-6 sm:min-w-80 sm:max-w-lg sm:rounded-lg"
        x-on:click.outside="modalOpen = false"
      >

        <!-- Header -->
        @if ($title)
          <div class="flex items-center justify-between pb-2">
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
            <button class="absolute right-0 top-0 mr-5 mt-5 flex h-8 w-8 items-center justify-center rounded-full text-neutral-600 hover:bg-neutral-50 hover:text-neutral-800"
              x-on:click="modalOpen = false"
            >
              <x-lucide-x class="h-5 w-5" />
            </button>
          </div>
        @endif

        <div class="relative w-auto">
          {{ $slot }}
        </div>
      </div>
    </div>
  </template>
</div>

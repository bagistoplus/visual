@props([
    'title' => null,
    'name' => null,
])

<div
  x-data
  @isset($name)x-modal="{name: @js($name)}" @else x-modal @endisset
  {{ $attributes }}
>
  <!-- Trigger -->
  @isset($trigger)
    <x-shop::ui.modal.trigger>
      {{ $trigger }}
    </x-shop::ui.modal.trigger>
  @endisset

  <template x-teleport="body">
    <div class="contents">
      <!-- Backdrop -->
      <div
        x-modal:backdrop
        class="fixed inset-0 z-[1000] bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition-opacity duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-300 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
      ></div>

      <!-- Positioner -->
      <div
        x-modal:positioner
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="fixed inset-0 z-[1100] grid h-screen w-screen place-content-center"
      >
        <!-- Modal Content -->
        <div x-modal:content class="bg-background relative min-w-96 overflow-hidden rounded-lg p-4 shadow-lg md:p-6">
          <!-- Close Button -->
          <x-shop::ui.button
            x-modal:close-trigger=""
            class="!absolute right-4 top-4"
            variant="ghost"
            color="secondary"
            size="sm"
            icon="lucide-x"
            icon-only
            rounded
          />

          @isset($title)
            <!-- Title -->
            <x-shop::ui.modal.title>
              {{ $title }}
            </x-shop::ui.modal.title>
          @endisset

          {{ $slot }}
        </div>
      </div>
    </div>
  </template>
</div>

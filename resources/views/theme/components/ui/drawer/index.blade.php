@props(['size' => 'sm', 'placement' => 'end', 'title' => null])

@php
  $sizeClasses = [
      'xs' => 'max-w-xs',
      'sm' => 'max-w-sm',
      'md' => 'max-w-md',
      'lg' => 'max-w-lg',
      'xl' => 'max-w-xl',
      'full' => 'max-w-full',
  ];

  // Position configuration map to avoid repetitive conditionals
  $placementConfigs = [
      'end' => [
          'positionner' => 'justify-end',
          'content' => $sizeClasses[$size] ?? $sizeClasses['md'],
          'enterStart' => 'translate-x-full',
          'enterEnd' => 'translate-x-0',
          'leaveStart' => 'translate-x-0',
          'leaveEnd' => 'translate-x-full',
      ],
      'start' => [
          'positionner' => 'justify-start',
          'content' => $sizeClasses[$size] ?? $sizeClasses['md'],
          'enterStart' => '-translate-x-full',
          'enterEnd' => 'translate-x-0',
          'leaveStart' => 'translate-x-0',
          'leaveEnd' => '-translate-x-full',
      ],
      'top' => [
          'positionner' => 'items-start',
          'content' => 'h-max-dvh h-max',
          'enterStart' => '-translate-y-full',
          'enterEnd' => 'translate-y-0',
          'leaveStart' => 'translate-y-0',
          'leaveEnd' => '-translate-y-full',
      ],
      'bottom' => [
          'positionner' => 'items-end',
          'content' => 'h-max-dvh h-max',
          'enterStart' => 'translate-y-full',
          'enterEnd' => 'translate-y-0',
          'leaveStart' => 'translate-y-0',
          'leaveEnd' => 'translate-y-full',
      ],
  ];

  // Get configuration for selected position or fallback to 'right'
  $config = $placementConfigs[$placement] ?? $placementConfigs['end'];
@endphp

<div
  x-data
  x-modal
  {{ $attributes }}
>
  @isset($trigger)
    <x-shop::ui.modal.trigger>
      {{ $trigger }}
    </x-shop::ui.modal.trigger>
  @endisset

  <template x-teleport="body">
    <div class="contents">
      <div x-modal:backdrop class="fixed inset-0 z-[1000] bg-black/50 backdrop-blur-sm"></div>

      <!-- Positioner (Drawer slides from right) -->
      <div x-modal:positioner class="{{ $config['positionner'] }} fixed inset-0 right-0 z-[1100] flex h-screen w-screen">
        <!-- Content -->
        <div
          x-show="$modal.open"
          x-modal:content
          x-transition:enter="transform transition ease-in-out duration-200 sm:duration-300"
          x-transition:enter-start="{{ $config['enterStart'] }}"
          x-transition:enter-end="{{ $config['enterEnd'] }}"
          x-transition:leave="transform transition ease-in-out duration-200 sm:duration-300"
          x-transition:leave-start="{{ $config['leaveStart'] }}"
          x-transition:leave-end="{{ $config['leaveEnd'] }}"
          class="bg-background {{ $config['content'] }} relative flex w-full flex-col shadow-xl"
        >
          <x-shop::ui.modal.close>
            <x-shop::ui.button
              variant="ghost"
              color="secondary"
              icon="lucide-x"
              size="sm"
              class="!absolute right-4 top-4"
              icon-only
              rounded
            />
          </x-shop::ui.modal.close>

          @isset($header)
            <header class="flex h-16 flex-none items-center">
              {{ $header }}
            </header>
          @endisset

          @isset($title)
            <header class="flex h-16 flex-none items-center p-4">
              <x-shop::ui.modal.title>
                {{ $title }}
              </x-shop::ui.modal.title>
            </header>
          @endisset

          <section class="flex-1 overflow-y-auto">
            {{ $slot }}
          </section>

          @isset($footer)
            <footer class="flex h-16 flex-none items-center">
              {{ $footer }}
            </footer>
          @endisset
        </div>
      </div>
    </div>
  </template>
</div>

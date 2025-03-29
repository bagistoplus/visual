@props(['position' => 'right', 'title'])

@php
  // Position configuration map to avoid repetitive conditionals
  $positionConfig = [
      'right' => [
          'panelWrapper' => 'fixed inset-y-0 right-0 flex max-w-full',
          'enterStart' => 'translate-x-full',
          'enterEnd' => 'translate-x-0',
          'leaveStart' => 'translate-x-0',
          'leaveEnd' => 'translate-x-full',
          'panelClasses' => 'w-auto min-w-48',
          'borderClass' => 'border-l',
      ],
      'left' => [
          'panelWrapper' => 'fixed top-0 bottom-0 left-0 flex max-w-full',
          'enterStart' => '-translate-x-full',
          'enterEnd' => 'translate-x-0',
          'leaveStart' => 'translate-x-0',
          'leaveEnd' => '-translate-x-full',
          'panelClasses' => 'w-auto min-w-48',
          'borderClass' => 'border-r',
      ],
      'top' => [
          'panelWrapper' => 'fixed inset-x-0 top-0 flex max-h-full',
          'enterStart' => '-translate-y-full',
          'enterEnd' => 'translate-y-0',
          'leaveStart' => 'translate-y-0',
          'leaveEnd' => '-translate-y-full',
          'panelClasses' => 'w-full h-auto',
          'borderClass' => 'border-b',
      ],
      'bottom' => [
          'panelWrapper' => 'fixed inset-x-0 bottom-0 flex max-h-full',
          'enterStart' => 'translate-y-full',
          'enterEnd' => 'translate-y-0',
          'leaveStart' => 'translate-y-0',
          'leaveEnd' => 'translate-y-full',
          'panelClasses' => 'w-full h-auto',
          'borderClass' => 'border-t',
      ],
  ];

  // Get configuration for selected position or fallback to 'right'
  $config = $positionConfig[$position] ?? $positionConfig['right'];

  // Common wrappers
  $outerWrapper = 'fixed inset-0 overflow-hidden';
  $innerWrapper = 'absolute inset-0 overflow-hidden';
@endphp

<div x-data="{
    open: false,
    init() {
        this.$watch('open', value => {
            if (value) {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        })
    }
}" {{ $attributes->merge(['class' => 'relative z-50 h-auto w-auto']) }}>
  <!-- Trigger -->
  <div @click="open = true">
    {{ $trigger }}
  </div>

  <!-- Slide Over -->
  <template x-teleport="body">
    <div
      x-show="open"
      x-cloak
      @keydown.window.escape="open = false"
      class="relative z-[99]"
    >
      <!-- Backdrop -->
      <div
        x-show="open"
        x-transition.opacity.duration.300ms
        class="fixed inset-0 bg-black/10"
        @click="open = false"
      ></div>

      <!-- Panel container -->
      <div class="{{ $outerWrapper }}">
        <div class="{{ $innerWrapper }}">
          <div class="{{ $config['panelWrapper'] }}">
            <div
              x-show="open"
              x-trap.inert.noscroll="open"
              x-transition:enter="transform transition ease-in-out duration-200 sm:duration-300"
              x-transition:enter-start="{{ $config['enterStart'] }}"
              x-transition:enter-end="{{ $config['enterEnd'] }}"
              x-transition:leave="transform transition ease-in-out duration-200 sm:duration-300"
              x-transition:leave-start="{{ $config['leaveStart'] }}"
              x-transition:leave-end="{{ $config['leaveEnd'] }}"
              class="{{ $config['panelClasses'] }}"
              @click.away="open = false"
            >
              <div class="{{ $config['borderClass'] }} bg-background flex h-full flex-col overflow-y-hidden border-neutral-100/70 shadow-lg">
                @isset($title)
                  <div class="flex flex-none items-center justify-between border-b border-neutral-200 px-4 py-2">
                    <h2 class="text-base font-semibold leading-6 text-neutral-800">
                      {{ $title }}
                    </h2>
                    <x-shop::ui.button
                      variant="ghost"
                      color="secondary"
                      icon="lucide-x"
                      size="sm"
                      icon-only
                      rounded
                      x-on:click="open = false"
                    />
                  </div>
                @endisset

                <!-- Content -->
                <div class="flex-1 overflow-y-hidden">
                  {{ $slot }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
</div>

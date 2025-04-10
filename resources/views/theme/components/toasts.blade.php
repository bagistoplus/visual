@pushOnce('scripts')
  <script>
    @foreach (session()->only(['success', 'error', 'warning', 'info']) as $type => $message)
      window.dispatchEvent(new CustomEvent('toasts:create', {
        detail: {
          type: '{{ $type }}',
          title: '{{ $message }}'
        }
      }));
    @endforeach
  </script>
@endpushOnce

<div x-data x-toasts="{placement: 'top-end'}">
  <template x-teleport="body">
    <template x-for="placement in placements" x-bind:key="placement">
      <div x-toasts:group="placement"
        class="fixed z-50 space-y-2 data-[align=center]:left-1/2 data-[align=end]:right-4 data-[align=start]:left-4 data-[side=bottom]:bottom-4 data-[side=top]:top-4 data-[align=center]:-translate-x-1/2"
      >
        <template x-for="toast in $group.toasts()" x-bind:key="toast.id">
          <div
            x-toasts:toast="toast"
            class="bg-background relative min-w-64 max-w-96 rounded-lg border-l-4 px-4 py-3 shadow-md"
            x-bind:class="{
                'data-[state=open]:animate-slide-in-down data-[state=closed]:animate-slide-out-up': $toast.placement.includes('top'),
                'data-[state=open]:animate-slide-in-up data-[state=closed]:animate-slide-out-down': $toast.placement.includes('bottom'),
                'bg-info-100 border-info-600 text-info-900': $toast.type === 'info',
                'bg-success-100 border-success-600 text-success-900': $toast.type === 'success',
                'bg-warning-100 border-warning-600 text-warning-900': $toast.type === 'warning',
                'bg-danger-100 border-danger-600 text-danger-900': $toast.type === 'error'
            }"
          >
            <div class="flex items-start justify-between">
              <div>
                <h3 class="text-sm font-semibold" x-text="$toast.title"></h3>
                <template x-if="$toast.description">
                  <p class="mt-1 text-sm" x-text="$toast.description"></p>
                </template>
              </div>
              <button
                x-toasts:toast-close-trigger
                class="absolute right-2 top-2 cursor-pointer rounded-full p-1"
                x-bind:class="{
                    'bg-info-100 hover:bg-info-200': $toast.type === 'info',
                    'bg-success-100 hover:bg-success-200': $toast.type === 'success',
                    'bg-warning-100 hover:bg-warning-200': $toast.type === 'warning',
                    'bg-danger-100 hover:bg-danger-200': $toast.type === 'error',
                }"
              >
                <span class="sr-only">Close</span>
                <x-lucide-x class="h-4 w-4" />
              </button>
            </div>
          </div>
        </template>
      </div>
    </template>
  </template>
</div>

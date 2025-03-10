@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('Toasts', () => ({
        toasts: [],
        positions: ['top-left', 'top-center', 'top-right', 'bottom-left', 'bottom-center', 'bottom-right'],

        init() {
          window.addEventListener('show-toast', (e) => {
            this.addToast(e.detail);
          });

          @foreach (session()->only(['success', 'error', 'warning', 'info']) as $type => $message)
            this.addToast({
              message: '{{ $message }}',
              type: '{{ $type }}'
            });
          @endforeach
        },

        addToast({
          message,
          type = 'info',
          position = 'top-right',
          duration = 3000
        }) {
          const id = Date.now();
          const toast = {
            id,
            message,
            type,
            position
          };

          this.toasts.push(toast);

          setTimeout(() => {
            this.removeToast(id);
          }, duration);
        },

        removeToast(id) {
          const toast = this.toasts.find(t => t.id === id);
          if (!toast) return;

          const el = document.querySelector(`[data-toast-id="${id}"]`);
          if (el) {
            const exitClass = this.getExitAnimation(toast.position);
            el.classList.remove(this.getEnterAnimation(toast.position));
            el.classList.add(exitClass);

            setTimeout(() => {
              this.toasts = this.toasts.filter(t => t.id !== id);
            }, 300);
          }
        },

        getTypeStyles(type) {
          const styles = {
            info: 'bg-info-200 border-info-600 text-info-900',
            success: 'bg-success-200 border-success-600 text-success-900',
            warning: 'bg-warning-200 border-warning-600 text-warning-900',
            danger: 'bg-danger-200 border-danger-600 text-danger-900'
          };
          return styles[type] || styles.info;
        },

        getButtonStyles(type) {
          const styles = {
            info: 'bg-info-200 hover:bg-info-300',
            success: 'bg-success-200 hover:bg-success-300',
            warning: 'bg-warning-200 hover:bg-warning-300',
            danger: 'bg-danger-200 hover:bg-danger-300'
          };
          return styles[type] || styles.info;
        },

        getPositionStyles(position) {
          const baseStyles = "fixed z-50 space-y-2";
          const positions = {
            'top-left': 'top-4 left-4',
            'top-center': 'top-4 left-1/2 -translate-x-1/2',
            'top-right': 'top-4 right-4',
            'bottom-left': 'bottom-4 left-4',
            'bottom-center': 'bottom-4 left-1/2 -translate-x-1/2',
            'bottom-right': 'bottom-4 right-4'
          };
          return `${baseStyles} ${positions[position]}`;
        },

        getEnterAnimation(position) {
          const animations = {
            'top-left': 'slide-in-left',
            'top-center': 'slide-in-down',
            'top-right': 'slide-in-right',
            'bottom-left': 'slide-in-left',
            'bottom-center': 'slide-in-up',
            'bottom-right': 'slide-in-right'
          };
          return animations[position] || 'slide-in-right';
        },

        getExitAnimation(position) {
          const animations = {
            'top-left': 'slide-out-left',
            'top-center': 'slide-out-up',
            'top-right': 'slide-out-right',
            'bottom-left': 'slide-out-left',
            'bottom-center': 'slide-out-down',
            'bottom-right': 'slide-out-right'
          };
          return animations[position] || 'slide-out-right';
        }
      }));
    });
  </script>
@endpushOnce

<div x-data="Toasts">
  <template x-for="position in positions" :key="position">
    <div :class="getPositionStyles(position)">
      <template x-for="toast in toasts.filter(t => t.position === position)" :key="toast.id">
        <div :data-toast-id="toast.id" x-show="true" :class="[getTypeStyles(toast.type), getEnterAnimation(toast.position)]"
          class="min-w-[300px] max-w-[420px] rounded-lg border-l-4 px-4 py-3 shadow-lg">
          <div class="flex items-start">
            <span x-text="toast.message"></span>
            <button class="-mx-1.5 -my-1.5 ml-auto inline-flex items-center justify-center rounded-lg p-1.5" x-bind:class="getButtonStyles(toast.type)"
              @click="removeToast(toast.id)">
              <span class="sr-only">Close</span>
              <x-lucide-x class="h-4 w-4" />
            </button>
          </div>
        </div>
      </template>
    </div>
  </template>
</div>

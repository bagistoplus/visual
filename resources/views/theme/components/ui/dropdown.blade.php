@props([
    // Allowed values: bottom-start, bottom-end, top-start, or top-end (default: bottom-start)
    'position' => 'bottom-start',
])

@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('VisualDropdown', (props) => ({
        opened: false,
        position: props.position,
        computedPosition: props.position,

        content: {
          'x-ref': 'content',
          ['x-bind:class']() {
            return 'absolute z-50 min-w-36 ' + this.computedClasses;
          },
          ['x-show']() {
            return this.opened
          },

          'x-transition:enter': 'ease-out duration-200',
          'x-transition:enter-end': 'translate-y-0',
          ['x-transition:enter-start']() {
            // When positioned at the bottom, slide down from above (-translate-y-2).
            // When at the top, slide up from below (translate-y-2).
            const [vertical] = this.computedPosition.split('-');
            return vertical === 'bottom' ? '-translate-y-2' : 'translate-y-2';
          },

          ['@click.away']() {
            this.opened = false;
          }
        },

        get computedClasses() {
          const positionMap = {
            'bottom-start': "start-0",
            'bottom-end': "end-0",
            'top-start': "start-0 bottom-full",
            'top-end': "end-0 bottom-full"
          };

          return positionMap[this.computedPosition] ?? '';
        },

        close() {
          this.opened = false;
        },

        open() {
          this.opened = true;
        },

        toggle() {
          this.opened = !this.opened;
        },

        init() {
          window.addEventListener('resize', this.recomputePosition);
          this.recomputePosition();
        },

        recomputePosition() {
          this.$nextTick(() => {
            if (!this.$refs.content || !this.$refs.trigger) return;

            const contentRect = this.$refs.content.getBoundingClientRect();
            const triggerRect = this.$refs.trigger.getBoundingClientRect();
            const [initVertical, initHorizontal] = this.position.split('-');

            // Compute available space.
            const spaceBelow = window.innerHeight - triggerRect.bottom;
            const spaceAbove = triggerRect.top;
            const spaceForStart = window.innerWidth - triggerRect.left;
            const spaceForEnd = triggerRect.right;

            // Vertical: if initial side isn’t viable, flip if alternative is.
            let vertical = initVertical;
            if (initVertical === 'bottom' && spaceBelow < contentRect.height) {
              vertical = spaceAbove >= contentRect.height ? 'top' : (spaceBelow >= spaceAbove ? 'bottom' :
                'top');
            } else if (initVertical === 'top' && spaceAbove < contentRect.height) {
              vertical = spaceBelow >= contentRect.height ? 'bottom' : (spaceAbove >= spaceBelow ? 'top' :
                'bottom');
            }

            // Horizontal: if initial side isn’t viable, flip if alternative is.
            let horizontal = initHorizontal;
            if (initHorizontal === 'start' && (triggerRect.left + contentRect.width > window.innerWidth)) {
              horizontal = triggerRect.right - contentRect.width >= 0 ? 'end' : 'start';
            } else if (initHorizontal === 'end' && (triggerRect.right - contentRect.width < 0)) {
              horizontal = triggerRect.left + contentRect.width <= window.innerWidth ? 'start' : 'end';
            }

            this.computedPosition = vertical + '-' + horizontal;
          });
        }
      }))
    });
  </script>
@endPushOnce

<div x-data="VisualDropdown(@js(['position' => $position]))" class="relative">
  <!-- Trigger slot -->
  <div x-ref="trigger" x-on:click="toggle(); recomputePosition()">
    {{ $trigger }}
  </div>

  <!-- Dropdown content slot -->
  <div x-bind="content" x-cloak>
    {{ $slot }}
  </div>
</div>

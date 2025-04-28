<div
  x-data="{ show: true }"
  x-show="show"
  class="bg-primary text-primary-50 relative truncate px-4 py-2 text-center text-sm"
>
  @if ($section->settings->link)
    <a href="{{ $section->settings->link }}" class="hover:underline">
      <span class="announcement-text">
        {{ $section->settings->text }}
      </span>
    </a>
    @svg('heroicon-o-arrow-right', ['class' => 'w-4 h-4 inline-block'])
  @else
    <p class="announcement-text">{{ $section->settings->text }}</p>
  @endif
  <button
    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:opacity-75"
    aria-label="Close"
    @click="show = false"
  >
    @svg('heroicon-o-x-mark', ['class' => 'w-4 h-4'])
  </button>
</div>

@if (ThemeEditor::inDesignMode())
  @pushOnce('scripts')
    <script>
      document.addEventListener('visual:editor:init', () => {
        window.Visual.on('section:updated', ({
          section
        }) => {
          if (section.type === '{{ $section->type }}') {
            console.log('updated do')
          }
        });

        window.Visual.handleLiveUpdate('{{ $section->type }}', {
          section: {
            text: {
              target: '.announcement-text',
              text: true
            }
          }
        });
      })
    </script>
  @endPushOnce
@endif

<x-admin::layouts>
  <x-slot:title>
    @lang('visual::admin.themes.title')
  </x-slot>

  <p class="text-xl font-bold text-gray-800 dark:text-white">
    @lang('visual::admin.themes.title')
  </p>

  @if ($themes->isEmpty())
    <div class="box-shadow mt-8 rounded bg-white p-4 dark:bg-gray-900 dark:text-gray-300">
      {{ __('visual::admin.themes.no-themes') }}
    </div>
  @else
    {{-- Room below the last row, so the Preview menu is not cut off by the admin's scroll area. --}}
    <div class="mt-8 grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-6 pb-24">
      @foreach ($themes as $theme)
        <div class="box-shadow flex flex-col rounded bg-white dark:bg-gray-900">
          <div class="flex aspect-video items-start justify-center overflow-hidden rounded-t bg-gray-100 dark:bg-gray-950">
            <img
              class="h-full w-full object-cover object-top"
              src="{{ $theme['screenshot'] ?? bagisto_asset('images/default_theme_preview.svg', 'visual_admin') }}"
              alt="{{ $theme['name'] }}"
            >
          </div>

          <div class="flex flex-1 flex-col gap-2 p-4">
            <p class="text-base font-semibold text-gray-800 dark:text-white">
              {{ $theme['name'] }}
            </p>

            @if ($theme['author'] || $theme['version'])
              <p class="text-xs text-gray-500 dark:text-gray-300">
                @if ($theme['author'])
                  @lang('visual::admin.themes.by') {{ $theme['author'] }}
                @endif

                @if ($theme['author'] && $theme['version'])
                  ·
                @endif

                @if ($theme['version'])
                  v{{ $theme['version'] }}
                @endif
              </p>
            @endif

            @if ($theme['description'])
              <p class="line-clamp-3 text-xs text-gray-600 dark:text-gray-300">
                {{ $theme['description'] }}
              </p>
            @endif

            @if ($theme['documentationUrl'])
              <a
                href="{{ $theme['documentationUrl'] }}"
                target="_blank"
                class="text-xs text-blue-600 dark:text-blue-300"
              >
                @lang('visual::admin.themes.view-documentation')
              </a>
            @endif

            <div class="mt-auto flex w-full flex-wrap items-center gap-2 pt-3">
              @if (ThemeEditor::allowedToEdit())
                <a href="{{ route('visual.admin.editor', ['theme' => $theme['code']]) }}" class="primary-button">
                  @lang('visual::admin.themes.customize')
                </a>
              @endif

              @if (ThemeEditor::allowedToPreview())
                <x-admin::dropdown class="[&_.absolute]:!shadow-md">
                  <x-slot:toggle>
                    <button type="button" class="secondary-button">
                      @lang('visual::admin.themes.preview')

                      <span class="icon-sort-down text-xl"></span>
                    </button>
                  </x-slot>

                  <x-slot:menu>
                    <x-admin::dropdown.menu.item class="!p-0">
                      <a
                        href="{{ route('shop.home.index', ['_previewMode' => $theme['code']]) }}"
                        target="_blank"
                        class="block px-5 py-2"
                      >
                        @lang('visual::admin.themes.preview-published')
                      </a>
                    </x-admin::dropdown.menu.item>

                    <x-admin::dropdown.menu.item class="!p-0">
                      <a
                        href="{{ route('shop.home.index', ['_draftPreview' => $theme['code']]) }}"
                        target="_blank"
                        class="block px-5 py-2"
                      >
                        @lang('visual::admin.themes.preview-draft')
                      </a>
                    </x-admin::dropdown.menu.item>
                  </x-slot>
                </x-admin::dropdown>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</x-admin::layouts>

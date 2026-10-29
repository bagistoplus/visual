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
    <div class="mt-8 grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-6">
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

            @if ($theme['isCopy'])
              <p class="text-xs text-gray-500 dark:text-gray-300">
                @lang('visual::admin.themes.based-on', ['name' => $theme['parentName']])
              </p>
            @endif

            @if ($theme['unavailable'])
              <p class="text-xs text-red-600 dark:text-red-400">
                @lang('visual::admin.themes.copy-unavailable')
              </p>
            @endif

            @if (!$theme['isCopy'] && ($theme['author'] || $theme['version']))
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
              @if (ThemeEditor::allowedToEdit() && !$theme['unavailable'])
                <a href="{{ route('visual.admin.editor', ['theme' => $theme['code']]) }}" class="primary-button">
                  @lang('visual::admin.themes.customize')
                </a>
              @endif

              @php
                $canPreview = ThemeEditor::allowedToPreview() && !$theme['unavailable'];
                $canDuplicate = $can['duplicate'] && $theme['isVisual'] && !$theme['unavailable'];
                $canEditCopy = $can['edit'] && $theme['isCopy'] && !$theme['unavailable'];
                $canDelete = $can['delete'] && $theme['isCopy'];
              @endphp

              @if ($canPreview || $canDuplicate || $canEditCopy || $canDelete)
                {{-- Opens upwards: the admin's scroll area would cut off a menu hanging below the last row. --}}
                <x-admin::dropdown position="top-left" class="[&_.absolute]:!bottom-full [&_.absolute]:!shadow-md">
                  <x-slot:toggle>
                    <button
                      type="button"
                      class="secondary-button !px-2"
                      aria-label="@lang('visual::admin.themes.more-actions')"
                    >
                      <span class="icon-dots text-xl"></span>
                    </button>
                  </x-slot>

                  <x-slot:menu class="!py-2">
                    @if ($canPreview)
                      <li class="px-5 pb-1 pt-2 text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">
                        @lang('visual::admin.themes.preview')
                      </li>

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
                    @endif

                    @if ($canPreview && ($canDuplicate || $canEditCopy || $canDelete))
                      <li class="my-2 border-t dark:border-gray-800"></li>
                    @endif

                    @if ($canDuplicate)
                      <x-admin::dropdown.menu.item class="!p-0">
                        <button
                          type="button"
                          class="block w-full px-5 py-2 text-left"
                          @click="$refs['duplicateThemeModal{{ $loop->index }}'].toggle()"
                        >
                          @lang('visual::admin.themes.duplicate')
                        </button>
                      </x-admin::dropdown.menu.item>
                    @endif

                    @if ($canEditCopy)
                      <x-admin::dropdown.menu.item class="!p-0">
                        <button
                          type="button"
                          class="block w-full px-5 py-2 text-left"
                          @click="$refs['editThemeCopyModal{{ $loop->index }}'].toggle()"
                        >
                          @lang('visual::admin.themes.edit')
                        </button>
                      </x-admin::dropdown.menu.item>
                    @endif

                    @if ($canDelete)
                      <x-admin::dropdown.menu.item class="!p-0">
                        <button
                          type="button"
                          class="block w-full px-5 py-2 text-left text-red-600 dark:text-red-400"
                          @click="$emitter.emit('open-confirm-modal', {
                            message: @js(trans('visual::admin.themes.delete-confirm')),
                            agree: () => $refs['deleteThemeCopy{{ $loop->index }}'].submit(),
                          })"
                        >
                          @lang('visual::admin.themes.delete')
                        </button>
                      </x-admin::dropdown.menu.item>
                    @endif
                  </x-slot>
                </x-admin::dropdown>
              @endif

              {{-- Kept out of the menu, which hides its contents once an item is clicked. --}}
              @if ($canDuplicate)
                @include('visual::admin.themes.copy-modal', [
                    'ref' => 'duplicateThemeModal' . $loop->index,
                    'action' => route('visual.admin.themes.duplicate', ['theme' => $theme['code']]),
                    'method' => 'POST',
                    'title' => trans('visual::admin.themes.duplicate-title', ['name' => $theme['name']]),
                    'submitLabel' => trans('visual::admin.themes.duplicate'),
                    'name' => trans('visual::admin.themes.copy-default-name', ['name' => $theme['name']]),
                    'description' => '',
                ])
              @endif

              @if ($canEditCopy)
                @include('visual::admin.themes.copy-modal', [
                    'ref' => 'editThemeCopyModal' . $loop->index,
                    'action' => route('visual.admin.themes.update', ['theme' => $theme['code']]),
                    'method' => 'PUT',
                    'title' => trans('visual::admin.themes.edit-title'),
                    'submitLabel' => trans('visual::admin.themes.save'),
                    'name' => $theme['name'],
                    'description' => $theme['description'] ?? '',
                ])
              @endif

              @if ($canDelete)
                <form
                  ref="deleteThemeCopy{{ $loop->index }}"
                  method="POST"
                  action="{{ route('visual.admin.themes.delete', ['theme' => $theme['code']]) }}"
                  class="hidden"
                >
                  @csrf
                  @method('DELETE')
                </form>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</x-admin::layouts>

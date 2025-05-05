<x-admin::layouts>
  <x-slot:title>
    @lang('visual::admin.themes.title')
  </x-slot>

  <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
    <p class="mt-2 text-xl font-bold text-gray-800 dark:text-white">
      @lang('visual::admin.themes.title')
    </p>

  </div>
  <div class="mt-8 max-w-3xl">
    @forelse($themes as  $theme)
      <div class="relative mb-6 min-h-[16rem] rounded border border-gray-200 bg-white px-8 pb-8 pt-8 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
        <div class="absolute inset-y-0 right-0 h-full w-[340px] bg-cover bg-center bg-no-repeat"
          style="background-image: url({{ $theme->previewImage ? $theme->url($theme->previewImage) : bagisto_asset('images/default_theme_preview.png', 'visual_admin') }})"
        >
        </div>
        <div class="relative w-full max-w-xs">
          <h3 class="text-2xl font-semibold">{{ $theme->name }}</h3>

          <div class="mt-2">
            @if ($theme->author)
              <p><span class="font-semibold">Made by:</span> {{ $theme->author }}</p>
            @endif
            @if ($theme->documentationUrl)
              <a
                href="{{ $theme->documentationUrl }}"
                target="_blank"
                class="text-blue-500"
              >
                View Documentation
              </a>
            @endif
          </div>
          <div class="mt-4 space-x-4">
            <a href="{{ route('visual.admin.editor', ['theme' => $theme->code]) }}" class="rounded bg-blue-600 px-5 py-2 text-white">
              {{ __('visual::admin.themes.customize') }}
            </a>
            <a
              href="{{ route('shop.home.index', ['_previewMode' => $theme->code]) }}"
              target="_blank"
              class="rounded border px-5 py-2 text-blue-600 shadow dark:border-gray-600 dark:bg-gray-800 dark:text-blue-300"
            >
              {{ __('visual::admin.themes.preview') }}
            </a>
          </div>
        </div>
      </div>
    @empty
      <div class="card">
        {{ __('arcade::theme-editor.themes.no-themes') }}
      </div>
    @endforelse
  </div>
</x-admin::layouts>

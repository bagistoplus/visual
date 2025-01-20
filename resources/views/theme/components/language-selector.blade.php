@props(['mobile'])

@php
  $locales = core()->getCurrentChannel()->locales()->orderBy('name')->get();
  $currentLocale = core()->getCurrentLocale();
@endphp

@if ($locales->count() > 0)
  @isset($mobile)
    <label for="mobile-locale" class="text-secondary mb-2 block text-sm font-medium">Language</label>
    <select id="mobile-locale" x-data
      @change="
        const url = new URL(window.location.href);
        url.searchParams.set('locale', $event.target.value);
        window.location.href = url.toString();
    ">
      @foreach ($locales as $locale)
        <option value="{{ $locale->code }}" @if ($locale->code === $currentLocale->code) selected @endif>
          {{ $locale->name }}
        </option>
      @endforeach
    </select>
  @else
    <div class="relative hidden sm:block" x-data="{ showLanguageMenu: false }"">
      <button class="text-secondary hover:text-primary flex items-center p-2 transition-colors"
        @click="showLanguageMenu = !showLanguageMenu">
        <x-heroicon-o-globe-alt class="h-5 w-5" />
        <span class="ml-1 uppercase">{{ $currentLocale->code }}</span>
      </button>
      <div x-show="showLanguageMenu" x-transition class="bg-surface absolute right-0 mt-2 w-48 rounded-lg py-2 shadow-lg"
        @click.outside="showLanguageMenu = false">
        @foreach ($locales as $locale)
          <a href="{{ request()->fullUrlWithQuery(['locale' => $locale->code]) }}"
            class="hover:bg-surface-alt block w-full px-4 py-2 text-left transition-colors">
            {{ $locale->name }}
          </a>
        @endforeach
      </div>
    </div>
  @endisset
@endif

@php
  $props = [
      'searchUrl' => route('shop.search.index'),
      'messages' => [
          'invalidFileType' => trans('shop::app.search.images.index.only-images-allowed'),
          'fileTooLarge' => trans('shop::app.search.images.index.size-limit-error'),
          'uploadFailed' => trans('shop::app.search.images.index.something-went-wrong'),
          'analysisFailed' => trans('shop::app.search.images.index.something-went-wrong'),
          'libraryLoadFailed' => trans('shop::app.search.images.index.something-went-wrong'),
      ],
  ];
@endphp

<div
  x-data
  x-image-search="@js($props)"
  {{ $attributes->merge(['class' => 'flex items-center']) }}
>
  <button
    x-image-search:trigger
    aria-label="@lang('shop::app.search.images.index.search')"
    class="hover:text-primary transition-colors"
    x-bind:class="{ 'cursor-not-allowed': $imageSearch.isSearching }"
  >
    <x-lucide-camera x-show="!$imageSearch.isSearching" class="h-5 w-5" />
    <x-lucide-loader-2 x-show="$imageSearch.isSearching" class="h-5 w-5 animate-spin" />
  </button>

  <input class="hidden" x-image-search:file-input />

  <img
    x-image-search:preview
    class="hidden"
    alt="uploaded image url"
    width="20"
    height="20"
  />
</div>

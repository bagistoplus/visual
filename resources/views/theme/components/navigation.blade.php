@props(['categories'])

@php
  $itemClass = 'group inline-flex h-10 w-max items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors  hover:text-neutral-900 focus:outline-none';
@endphp

<div
  x-data
  x-navigation
  {{ $attributes->merge(['class' => 'h-full flex items-center']) }}
>
  <div class="relative">
    <ul class="group flex flex-1 list-none items-center justify-center space-x-2 rounded-md p-1.5 text-neutral-700">
      @foreach ($categories as $category)
        <li>
          @if ($category->children->isEmpty())
            <a href="{{ $category->url }}" class="{{ $itemClass }} hover:bg-neutral-100">
              {{ $category->name }}
            </a>
          @else
            <a
              href="{{ $category->url }}"
              class="{{ $itemClass }}"
              x-navigation:item="{{ $category->id }}"
              x-bind:class="{ 'bg-neutral-100': $item.isActive, 'hover:bg-neutral-100': !$item.isActive }"
            >
              {{ $category->name }}
            </a>
          @endif
        </li>
      @endforeach
    </ul>
  </div>

  <div
    x-navigation:dropdown
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90"
    class="absolute top-full pt-1 duration-200 ease-out"
    x-cloak
  >
    <div class="bg-background flex h-auto w-auto justify-center overflow-hidden rounded-md border border-neutral-200/70 shadow-sm">
      @foreach ($categories as $category)
        @if ($category->children->isNotEmpty())
          <div x-navigation:section="{{ $category->id }}" class="flex w-full max-w-3xl items-stretch justify-center gap-x-3 p-4">
            @if ($category->logo_url || $category->banner_url)
              <div class="relative flex h-full min-h-64 w-40 flex-shrink-0 items-end overflow-hidden rounded bg-cover bg-center bg-no-repeat p-4"
                style="background-image: url({{ $category->logo_url ?? $category->banner_url }})"
              >
                <div class="absolute inset-0 bg-neutral-900/65"></div>
                <div class="relative space-y-1.5 text-neutral-50">
                  <span class="block text-lg font-bold">{{ $category->name }}</span>
                  <span class="block text-sm opacity-90">{!! $category->description !!}</span>
                </div>
              </div>
            @endif

            <div class="flex-1">
              <div class="flex flex-wrap gap-4">
                @foreach ($category->children as $subCategory)
                  <div class="w-64 flex-none">
                    <a
                      x-navigation:sub-item
                      href="{{ $subCategory->url }}"
                      class="block rounded px-3.5 py-3 text-sm hover:bg-neutral-100"
                    >
                      <span class="mb-1 block font-medium text-neutral-900">
                        {{ $subCategory->name }}
                      </span>
                      @if ($subCategory->description)
                        <span class="block truncate leading-5 text-neutral-400">
                          {!! $subCategory->description !!}
                        </span>
                      @endif
                    </a>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endif
      @endforeach
    </div>
  </div>
</div>

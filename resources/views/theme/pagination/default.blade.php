@php
  if (!isset($scrollTo)) {
      $scrollTo = 'body';
  }

  $scrollIntoViewJsSnippet =
      $scrollTo !== false
          ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()"
          : '';
@endphp

@if ($paginator->hasPages())
  <nav
    role="navigation"
    aria-label="{{ __('Pagination Navigation') }}"
    class="flex items-center justify-between"
  >
    <div class="flex flex-1 justify-between sm:hidden">
      @if ($paginator->onFirstPage())
        <span
          class="text text-neutral relative inline-flex cursor-default items-center rounded-md border bg-neutral-50 px-4 py-2 text-sm font-medium leading-5"
        >
          {!! __('pagination.previous') !!}
        </span>
      @else
        <button
          type="button"
          href="{{ $paginator->previousPageUrl() }}"
          wire:loading.attr="disabled"
          wire:click.prevent="previousPage('{{ $paginator->getPageName() }}')"
          x-on:click.prevent="{{ $scrollIntoViewJsSnippet }}"
          dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
          class="bg-surface text-neutral border-surface-600 focus:ring-primary focus:border-primary hoverborder-primary active:bg-surface-100 relative inline-flex items-center rounded-md border px-4 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none active:text-neutral-700"
        >
          {!! __('pagination.previous') !!}
        </button>
      @endif

      @if ($paginator->hasMorePages())
        <button
          type="button"
          href="{{ $paginator->nextPageUrl() }}"
          wire:loading.attr="disabled"
          wire:click.prevent="nextPage('{{ $paginator->getPageName() }}')"
          x-on:click.prevent="{{ $scrollIntoViewJsSnippet }}"
          dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
          class="bg-surface text-neutral border-surface-600 focus:ring-primary focus:border-primary hoverborder-primary active:bg-surface-100 relative inline-flex items-center rounded-md border px-4 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none active:text-neutral-700"
        >
          {!! __('pagination.next') !!}
        </button>
      @else
        <span
          class="text text-neutral relative inline-flex cursor-default items-center rounded-md border bg-neutral-50 px-4 py-2 text-sm font-medium leading-5"
        >
          {!! __('pagination.next') !!}
        </span>
      @endif
    </div>

    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-neutral text-sm leading-5">
          {!! __('Showing') !!}
          @if ($paginator->firstItem())
            <span class="font-medium">{{ $paginator->firstItem() }}</span>
            {!! __('to') !!}
            <span class="font-medium">{{ $paginator->lastItem() }}</span>
          @else
            {{ $paginator->count() }}
          @endif
          {!! __('of') !!}
          <span class="font-medium">{{ $paginator->total() }}</span>
          {!! __('results') !!}
        </p>
      </div>

      <div>
        <span class="relative z-0 inline-flex gap-1.5 rounded-md">
          {{-- Previous Page Link --}}
          @if ($paginator->onFirstPage())
            <span
              role="button"
              aria-disabled="true"
              aria-label="{{ __('pagination.previous') }}"
            >
              <span
                class="relative inline-flex cursor-default items-center rounded-md px-2 py-2 text-sm font-medium leading-5 text-neutral-300 hover:bg-neutral-50"
                aria-hidden="true"
              >
                <svg
                  class="h-5 w-5"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                    clip-rule="evenodd"
                  />
                </svg>
              </span>
            </span>
          @else
            <button
              rel="prev"
              type="button"
              href="{{ $paginator->previousPageUrl() }}"
              wire:click.prevent="previousPage('{{ $paginator->getPageName() }}')"
              x-on.prevent:click="{{ $scrollIntoViewJsSnippet }}"
              dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
              class="text-neutral ring-primary focus:border-primary relative inline-flex items-center rounded-md px-2 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out hover:bg-neutral-100 focus:z-10 focus:outline-none focus:ring active:bg-neutral-50"
              aria-label="{{ __('pagination.previous') }}"
            >
              <svg
                class="h-5 w-5"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          @endif

          {{-- Pagination Elements --}}
          @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
              <span aria-disabled="true">
                <span
                  class="text-neutral relative -ml-px inline-flex cursor-default items-center bg-neutral-50 px-4 py-2 text-sm font-medium leading-5"
                >{{ $element }}</span>
              </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
              @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                  <span aria-current="page"
                    wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}"
                  >
                    <span
                      class="bg-primary text-primary-100 relative inline-flex cursor-default items-center rounded-md px-4 py-2 text-sm font-medium leading-5"
                    >{{ $page }}</span>
                  </span>
                @else
                  <button
                    type="button"
                    href="{{ $url }}"
                    wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}"
                    wire:click.prevent="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                    x-on:click.prevent="{{ $scrollIntoViewJsSnippet }}"
                    class="text-neutral ring-primary relative inline-flex items-center rounded-md px-4 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out hover:bg-neutral-100 focus:z-10 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-700"
                    aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                  >
                    {{ $page }}
                  </button>
                @endif
              @endforeach
            @endif
          @endforeach

          {{-- Next Page Link --}}
          @if ($paginator->hasMorePages())
            <button
              rel="next"
              type="button"
              href="{{ $paginator->nextPageUrl() }}"
              wire:click.prevent="nextPage('{{ $paginator->getPageName() }}')"
              x-on:click.prevent="{{ $scrollIntoViewJsSnippet }}"
              dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
              class="text-neutral ring-primary focus:border-primary relative inline-flex items-center rounded-md px-2 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out hover:bg-neutral-100 focus:z-10 focus:outline-none focus:ring active:bg-neutral-50"
              aria-label="{{ __('pagination.next') }}"
            >
              <svg
                class="h-5 w-5"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          @else
            <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
              <span
                class="relative inline-flex cursor-default items-center rounded-md px-2 py-2 text-sm font-medium leading-5 text-neutral-300 hover:bg-neutral-50"
                aria-hidden="true"
              >
                <svg
                  class="h-5 w-5"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                    clip-rule="evenodd"
                  />
                </svg>
              </span>
            </span>
          @endif
        </span>
      </div>
    </div>
  </nav>
@endif

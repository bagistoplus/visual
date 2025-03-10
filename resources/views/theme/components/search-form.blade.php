<button
  class="p-2"
  aria-label="Search"
  @click="showSearch = !showSearch"
>
  <x-lucide-search class="hover:text-primary h-5 w-5 transition-colors" />
</button>

<div
  x-cloak
  x-show="showSearch"
  class="bg-surface border-surface-600 absolute -left-4 right-0 top-16 border-b shadow-lg"
  @click.outside="showSearch = false"
>
  <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
    <form
      method="get"
      action="{{ route('shop.search.index') }}"
      class="relative mx-auto max-w-3xl"
    >
      @foreach (collect(request()->query())->except('query') as $key => $value)
        <input
          type="hidden"
          name="{{ $key }}"
          value="{{ $value }}"
        >
      @endforeach
      <x-lucide-search class="hover:text-primary absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 transition-colors" />

      <input
        type="search"
        name="query"
        value="{{ old('query') }}"
        minlength="{{ core()->getConfigData('catalog.products.search.min_query_length') }}"
        maxlength="{{ core()->getConfigData('catalog.products.search.max_query_length') }}"
        placeholder="@lang('shop::app.components.layouts.header.search-text')"
        aria-label="@lang('shop::app.components.layouts.header.search-text')"
        aria-required="true"
        pattern="[^\\]+"
        required
        class="focus:ring-primary rounded-full pl-12 pr-10 focus:border-transparent focus:outline-none focus:ring-2"
      >

      {{-- <button type="submit" class="hover:text-primary absolute right-4 top-1/2 -translate-y-1/2 transition-colors">
        <x-lucide-x class="h-5 w-5" />
      </button> --}}
    </form>
  </div>
</div>

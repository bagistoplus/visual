@props(['product'])
<div class="space-y-4">
  @if ($product->downloadable_samples->isNotEmpty())
    <div class="">
      <h3 class="mb-2 text-base font-semibold text-neutral-900">@lang('shop::app.products.view.type.downloadable.samples')</h3>

      <ul>
        @foreach ($product->downloadable_samples as $sample)
          <li>
            <a
              class="text-primary"
              href="{{ route('shop.downloadable.download_sample', ['type' => 'sample', 'id' => $sample->id]) }}"
              target="_blank"
            >
              {{ $sample->title }}
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  @endif

  @if ($product->downloadable_links->isNotEmpty())
    <div>
      <h3 class="mb-2 text-base font-semibold text-neutral-900">
        @lang('shop::app.products.view.type.downloadable.links')
      </h3>

      <div class="grid gap-1 md:gap-2">
        @foreach ($product->downloadable_links as $link)
          <div class="flex items-center gap-x-2">
            <label class="flex items-center gap-x-2">
              <input
                type="checkbox"
                name="downloadable_links[]"
                value="{{ $link->id }}"
                class="text-primary"
              >
              <span>{{ $link->title . ' + ' . core()->currency($link->price) }}</span>

              @if ($link->sample_file || $link->sample_url)
                <a
                  href="{{ route('shop.downloadable.download_sample', ['type' => 'link', 'id' => $link->id]) }}"
                  target="_blank"
                  class="text-primary ml-2 text-sm"
                >
                  @lang('shop::app.products.view.type.downloadable.sample')
                </a>
              @endif
            </label>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>

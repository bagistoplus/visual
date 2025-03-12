<div x-data="VisualProductDetails" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
  <form x-on:submit.prevent>
    <div class="relative flex flex-col items-start gap-12 md:flex-row">
      <x-shop::product.medias-gallery :medias="$medias" class="w-full flex-1 md:sticky md:top-4" />

      <div class="flex-1 space-y-6">
        @foreach ($blocksOnRight as $block)
          @include('shop::partials.product-details.render-block', ['block' => $block])
        @endforeach
      </div>
    </div>

    @if (count($blocksOnBottom) > 0)
      <div class="mt-6 space-y-6">
        @foreach ($blocksOnBottom as $block)
          @include('shop::partials.product-details.render-block', ['block' => $block])
        @endforeach
      </div>
    @endif
  </form>
</div>

@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', function() {
      Alpine.data('VisualProductDetails', () => ({}));
    })
  </script>
@endpushOnce

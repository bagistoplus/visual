@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('VisualAddToCompare', (productId) => ({
        productId: productId,
        userLoggedIn: @json(auth()->check()),

        handle() {
          if (this.userLoggedIn) {
            this.$wire.call('handle');
            return;
          }

          localStoredCompareItems = JSON.parse(localStorage.getItem('compare_items')) || [];

          if (localStoredCompareItems.includes(this.productId)) {
            this.$dispatch('show-toast', {
              type: 'warning',
              message: "@lang('shop::app.products.view.already-in-compare')"
            });
          } else {
            localStoredCompareItems.push(this.productId);
            localStorage.setItem('compare_items', JSON.stringify(localStoredCompareItems));
            this.$dispatch('show-toast', {
              type: 'success',
              message: "@lang('shop::app.products.view.add-to-compare')"
            });
          }
        }
      }));
    });
  </script>
@endPushOnce

<x-shop::ui.button
  x-data="VisualAddToCompare({{ $productId }})"
  x-on:click="handle"
  wire:loading.attr="disabled"
  title="{{ trans('shop::app.components.products.card.add-to-compare') }}"
  variant="soft"
  color="secondary"
  icon="lucide-arrow-left-right"
  icon-only
  rounded
  {{ $attributes }}
/>

<x-shop::ui.button
  wire:loading.attr="disabled"
  wire:click="handle"
  variant="soft"
  icon-only
  rounded
  :color="$inUserWishlist ? 'danger' : 'secondary'"
  :icon="$inUserWishlist ? 'heroicon-s-heart' : 'heroicon-o-heart'"
  {{ $attributes }}
/>

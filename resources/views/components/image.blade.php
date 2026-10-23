{{--
  An image uploaded through the editor, at the size the screen needs.
  Lazy unless `priority` is set, for the image that is likely the largest one in view.
  A lazy image lets the browser size it from its layout (`sizes="auto"`), with the
  full viewport where that is not supported; an eager one needs `sizes` from the caller.
--}}
@props(['image', 'sizes' => null, 'priority' => false, 'alt' => null])

@if ($image)
  @php
    $srcset = $image->srcset();
  @endphp

  <img
    src="{{ $image->url }}"
    @if ($srcset)
      srcset="{{ $srcset }}"
      sizes="{{ $sizes ?? ($priority ? '100vw' : 'auto, 100vw') }}"
    @endif
    @if ($image->width && $image->height)
      width="{{ $image->width }}"
      height="{{ $image->height }}"
    @endif
    alt="{{ $alt ?? $image->alt }}"
    @if ($priority)
      fetchpriority="high"
    @else
      loading="lazy"
    @endif
    decoding="async"
    {{ $attributes }}
  >
@endif

@props([
    'variant' => 'solid',
    'color' => 'primary',
    'size' => 'md',
    'iconOnly' => false,
    'icon' => null,
    'href' => null,
    'rounded' => false,
    'ariaLabel' => '', // Optional: for icon-only buttons
])

@php
  $baseClasses = 'relative border inline-flex items-center justify-center flex-row rtl:flex-row-reverse font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';

  // Size classes: if iconOnly, use fixed width and height; otherwise, use padding.
  if ($iconOnly) {
      $iconOnlySizes = [
          'sm' => 'w-6 h-6 text-xs',
          'sm' => 'w-8 h-8 text-sm',
          'md' => 'w-10 h-10 text-base',
          'lg' => 'w-12 h-12 text-lg',
      ];
      $sizeClasses = $iconOnlySizes[$size] ?? 'w-10 h-10 text-base';
  } else {
      $sizeVariants = [
          'xs' => 'px-1.5 py-0.5 text-xs',
          'sm' => 'px-3 py-1.5 text-sm',
          'md' => 'px-4 py-2 text-base',
          'lg' => 'px-6 py-3 text-lg',
      ];
      $sizeClasses = $sizeVariants[$size] ?? 'px-4 py-2 text-base';
  }

  $radiusClasses = $rounded ? 'rounded-full' : 'rounded-md';

  $disabledClasses = 'disabled:opacity-50 disabled:cursor-not-allowed';

  $variants = [
      'solid' => [
          'primary' => 'bg-primary-500 border-primary-500 text-primary-50 hover:bg-primary-600 focus:ring-primary-500',
          'secondary' => 'bg-secondary-500 border-secondary-500 text-secondary-50 hover:bg-secondary-600 focus:ring-secondary-500',
          'neutral' => 'bg-neutral-500 border-neutral-500 text-neutral-50 hover:bg-neutral-600 focus:ring-neutral-500',
          'danger' => 'bg-danger-500 border-danger-500 text-danger-50 hover:bg-danger-600 focus:ring-danger-500',
          'warning' => 'bg-warning-500 border-warning-500 text-warning-50 hover:bg-warning-600 focus:ring-warning-500',
          'success' => 'bg-success-500 border-success-500 text-success-50 hover:bg-success-600 focus:ring-success-500',
      ],
      'soft' => [
          'primary' => 'bg-primary-100 border-primary-100 text-primary-700 hover:bg-primary-200 focus:ring-primary-500',
          'secondary' => 'bg-secondary-100 border-secondary-100 text-secondary-700 hover:bg-secondary-200 focus:ring-secondary-500',
          'neutral' => 'bg-neutral-100 border-neutral-100 text-neutral-700 hover:bg-neutral-200 focus:ring-neutral-500',
          'danger' => 'bg-danger-100 border-danger-100 text-danger-700 hover:bg-danger-200 focus:ring-danger-500',
          'warning' => 'bg-warning-100 border-warning-100 text-warning-700 hover:bg-warning-200 focus:ring-warning-500',
          'success' => 'bg-success-100 border-success-100 text-success-700 hover:bg-success-200 focus:ring-success-500',
      ],
      'outline' => [
          'primary' => 'border-primary-500 text-primary-500 hover:bg-primary-50 focus:ring-primary-500',
          'secondary' => 'border-secondary-500 text-secondary-500 hover:bg-secondary-50 focus:ring-secondary-500',
          'neutral' => 'border-neutral-200 text-neutral-500 hover:bg-neutral-100 focus:ring-neutral-500',
          'danger' => 'border-danger-500 text-danger-500 hover:bg-danger-50 focus:ring-danger-500',
          'warning' => 'border-warning-500 text-warning-500 hover:bg-warning-50 focus:ring-warning-500',
          'success' => 'border-success-500 text-success-500 hover:bg-success-50 focus:ring-success-500',
      ],
      'ghost' => [
          'primary' => 'border-transparent text-primary-500 hover:bg-primary-50 focus:ring-primary-500',
          'secondary' => 'border-transparent text-secondary-500 hover:bg-secondary-50 focus:ring-secondary-500',
          'danger' => 'border-transparent text-danger-500 hover:bg-danger-50 focus:ring-danger-500',
          'warning' => 'border-transparent text-warning-500 hover:bg-warning-50 focus:ring-warning-500',
          'success' => 'border-transparent text-success-500 hover:bg-success-50 focus:ring-success-500',
      ],
      'link' => [
          'primary' => 'border-transparent text-primary-500 hover:underline focus:ring-primary-500',
          'secondary' => 'border-transparent text-secondary-500 hover:underline focus:ring-secondary-500',
          'danger' => 'border-transparent text-danger-500 hover:underline focus:ring-danger-500',
          'warning' => 'border-transparent text-warning-500 hover:underline focus:ring-warning-500',
          'success' => 'border-transparent text-success-500 hover:underline focus:ring-success-500',
      ],
  ];
  $variantClasses = $variants[$variant][$color] ?? $variants['solid']['primary'];

  $transitionClasses = 'transition-all duration-200';

  $classesArray = [$baseClasses, $sizeClasses, $radiusClasses, $variantClasses, $disabledClasses, $transitionClasses];
  $classes = implode(' ', array_filter($classesArray));

  $wireTarget = $attributes->has('wire:target') ? 'wire:target=' . $attributes->get('wire:target') : '';

  $iconSizes = [
      'sm' => 'h-4 w-4',
      'md' => 'h-5 w-5',
      'lg' => 'h-6 w-6',
  ];
  $iconSizeClasses = $iconSizes[$size] ?? 'h-5 w-5';

  // When not icon-only, add margin for the icon (if text is present)
  $iconMarginClass = $slot->isEmpty() || $iconOnly ? '' : 'mr-2 rtl:ml-2 -ml-1 rtl:-mr-1';

  $tag = $href ? 'a' : 'button';
@endphp

{{-- @if ($href) --}}
<{{ $tag }}
  @if ($href) href="{{ $href }}" @endif
  {{ $attributes->merge(['class' => $classes]) }}
  @if ($icon && $slot->isEmpty() && $ariaLabel) aria-label="{{ $ariaLabel }}" @endif
  wire:loading.attr="disabled"
>
  @if ($icon)
    <span
      wire:loading.class="opacity-0"
      {!! $wireTarget !!}
      class="{{ $iconMarginClass }} inline-flex h-5 w-5 items-center justify-center transition-opacity duration-200"
    >
      @svg($icon, ['class' => $iconSizeClasses])
    </span>
  @endif

  @if (!$iconOnly)
    <span
      wire:loading.class="opacity-0"
      {!! $wireTarget !!}
      class="transition-opacity duration-200"
    >
      {{ $slot }}
    </span>
  @endif

  <span
    wire:loading.inline-flex
    {!! $wireTarget !!}
    class="absolute inset-0 inline-flex h-full w-full items-center justify-center"
  >
    <x-lucide-loader-2 class="{{ $iconSizeClasses }} animate-spin" />
  </span>
  </{{ $tag }}>

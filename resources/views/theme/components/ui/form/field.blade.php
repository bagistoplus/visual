@props([
    'label' => '',
    'name' => '',
    'id' => null,
    'size' => 'md',
    'errorName' => '',
    'prepend' => null,
    'append' => null,
    'prependIcon' => null,
    'appendIcon' => null,
])

@php
  use Illuminate\Support\Str;

  // Generate an ID based on the field name if provided, else use a random one.
  $fieldId = $id ?: ($name ? 'form-field-' . Str::slug($name) . '-' . Str::random(4) : 'field-' . Str::random(6));

  // Map field size to icon size classes.
  $iconSizes = [
      'sm' => 'w-4 h-4',
      'md' => 'w-5 h-5',
      'lg' => 'w-6 h-6',
  ];
  $iconClass = $iconSizes[$size] ?? $iconSizes['md'];
@endphp

<div {{ $attributes }}>
  @if ($label)
    <x-shop::ui.form.label for="{{ $fieldId }}" :required="$attributes->get('required', false)">
      {{ $label }}
    </x-shop::ui.form.label>
  @endif

  <div class="relative">
    {{ $slot }}

    @if ($prependIcon)
      <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <x-icon :name="$prependIcon" :class="$iconClass" />
      </div>
    @elseif($prepend)
      {{ $prepend }}
    @endif

    @if ($appendIcon)
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-600">
        <x-icon :name="$appendIcon" :class="$iconClass" />
      </div>
    @elseif($append)
      {{ $append }}
    @endif
  </div>

  <x-shop::ui.form.error id="{{ $fieldId }}" name="{{ $name }}" />
</div>

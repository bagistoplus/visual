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

  $sizeClasses = [
      'sm' => 'text-sm px-2 py-1.5',
      'md' => 'text-base px-4 py-2',
      'lg' => 'text-lg px-5 py-3',
  ];
  $baseSelectClass = $sizeClasses[$size] ?? $sizeClasses['md'];

  $prependPadding = [
      'sm' => 'pl-8',
      'md' => 'pl-10',
      'lg' => 'pl-12',
  ];
  $appendPadding = [
      'sm' => 'pr-8',
      'md' => 'pr-10',
      'lg' => 'pr-12',
  ];
  $extraPadding =
      ($prependIcon ? ' ' . ($prependPadding[$size] ?? 'pl-10') : '') .
      ($appendIcon ? ' ' . ($appendPadding[$size] ?? 'pr-10') : '');
@endphp

<x-shop::ui.form.field
  :label="$label"
  :name="$name"
  :id="$fieldId"
  :size="$size"
  :errorName="$errorName"
  :prepend="$prepend"
  :append="$append"
  :prependIcon="$prependIcon"
  :appendIcon="$appendIcon"
  :required="$attributes->has('required')"
>
  <select
    id="{{ $fieldId }}"
    name="{{ $name }}"
    aria-invalid="{{ $errors->has($errorName ?? $name) ? 'true' : 'false' }}"
    @if ($errors->has($name)) aria-describedby="{{ $fieldId }}-error" @endif
    {{ $attributes->merge([
        'class' => trim("$baseSelectClass $extraPadding w-full"),
    ]) }}
  >
    {{ $slot }}
  </select>
</x-shop::ui.form.field>

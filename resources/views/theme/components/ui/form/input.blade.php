@props([
    'label' => '',
    'name' => '',
    'id' => null,
    'type' => 'text',
    'size' => 'md', // Options: sm, md, lg
    'errorName' => '',
    'prepend' => null,
    'append' => null,
    'prependIcon' => null,
    'appendIcon' => null,
])

@php
  // Generate an ID based on the field name if provided, else use a random one.
  $fieldId = $id ?: ($name ? 'form-field-' . Str::slug($name) . '-' . Str::random(4) : 'field-' . Str::random(6));

  $sizeClasses = [
      'sm' => 'text-sm px-2 py-1.5',
      'md' => 'text-base px-4 py-2',
      'lg' => 'text-lg px-5 py-3',
  ];
  $baseInputClass = $sizeClasses[$size] ?? $sizeClasses['md'];

  // Extra padding classes when an icon is present.
  $prependPadding = [
      'sm' => 'pl-6',
      'md' => 'pl-10',
      'lg' => 'pl-12',
  ];

  $appendPadding = [
      'sm' => 'pr-6',
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
  <input
    id="{{ $fieldId }}"
    type="{{ $type }}"
    name="{{ $name }}"
    aria-invalid="{{ $errors->has($errorName ?? $name) ? 'true' : 'false' }}"
    @if ($errors->has($name)) aria-describedby="{{ $fieldId }}-error" @endif
    {{ $attributes->merge([
        'class' => trim("$baseInputClass $extraPadding w-full"),
    ]) }}
  />
</x-shop::ui.form.field>

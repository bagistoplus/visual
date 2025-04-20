@props(['for' => '', 'required' => false])

<label
  @if ($for) for="{{ $for }}" @endif
  @if ($required) required @endif
  class="mb-1 block text-sm font-medium text-neutral-600"
>
  {{ $slot }}
</label>

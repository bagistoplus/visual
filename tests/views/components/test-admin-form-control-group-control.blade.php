@props([
    'type' => 'text',
    'name' => null,
    'value' => null,
])

@if ($type === 'select')
    <select name="{{ $name }}" value="{{ $value }}">
        {{ $slot }}
    </select>
@else
    <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" />
@endif

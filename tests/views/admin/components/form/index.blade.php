<form action="{{ $attributes->get('action') }}" data-method="{{ $attributes->get('method', 'POST') }}">
{{ $slot }}
</form>

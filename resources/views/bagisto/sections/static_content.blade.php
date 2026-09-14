@if (! empty($data['css']))
    @push ('styles')
        <style>
            {!! $data['css'] !!}
        </style>
    @endpush
@endif

@if (! empty($data['html']))
    {!! $data['html'] !!}
@endif

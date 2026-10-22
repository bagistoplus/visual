{{--
    A release that stores media as references in the markup resolves them as it
    draws the section; an earlier one stores urls and has nothing to resolve.
--}}
@php
    $themeStorage = class_exists('Webkul\Theme\ThemeStorage') ? app('Webkul\Theme\ThemeStorage') : null;
    $resolve = fn (string $markup) => $themeStorage ? $themeStorage->resolveMarkup($markup) : $markup;
@endphp

@if (! empty($data['css']))
    @push ('styles')
        <style>
            {!! $resolve($data['css']) !!}
        </style>
    @endpush
@endif

@if (! empty($data['html']))
    {!! $resolve($data['html']) !!}
@endif

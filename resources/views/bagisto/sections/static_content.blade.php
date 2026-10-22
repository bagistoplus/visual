{{--
    A release that stores media as references in the markup resolves them as it
    draws the section; an earlier one stores urls and has nothing to resolve,
    whether or not it has a storage object.
--}}
@php
    $themeStorage = class_exists('Webkul\Theme\ThemeStorage') ? app('Webkul\Theme\ThemeStorage') : null;
    $resolves = $themeStorage && method_exists($themeStorage, 'resolveMarkup');
    $resolve = fn (string $markup) => $resolves ? $themeStorage->resolveMarkup($markup) : $markup;
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

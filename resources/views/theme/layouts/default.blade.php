@php
  $direction = core()->getCurrentLocale()->direction;
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $direction }}">

  <head>
    {!! view_render_event('bagisto.shop.layout.head.before') !!}

    @include('shop::partials.head')

    {!! $theme->settings->font->toHtml() !!}
    <style>
      body {
        font-family: {{ $theme->settings->font }};
      }
    </style>

    {!! view_render_event('bagisto.shop.layout.head.after') !!}
  </head>

  <body class="{{ $direction }} style="scroll-behavior: smooth;">
    {!! view_render_event('bagisto.shop.layout.body.before') !!}

    <x-shop::toasts />

    <x-shop::confirm-modal />

    {!! view_render_event('bagisto.shop.layout.content.before') !!}

    <main role="main" tabindex="-1">
      <visual:section name="visual::announcement-bar" />
      <visual:section name="visual::header" />

      @section('body')
        @visual_layout_content
      @show

      <visual:section name="visual::footer" />
    </main>

    {!! view_render_event('bagisto.shop.layout.content.after') !!}

    {!! view_render_event('bagisto.shop.layout.body.after') !!}

    @stack('scripts')
    @livewireScriptConfig
  </body>

</html>

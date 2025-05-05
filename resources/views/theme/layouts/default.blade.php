@php
  $direction = core()->getCurrentLocale()->direction;
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $direction }}">

  <head>
    <title>@yield('page_title', config('app.name'))</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="content-language" content="{{ app()->getLocale() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="currency" content="{{ core()->getCurrentCurrencyCode() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script type="application/ld+json" id="currency-data">
      @json(core()->getCurrentCurrency()->toArray())
    </script>

    @stack('styles')
  </head>

  <body class="{{ $direction }} style="scroll-behavior: smooth;">

    <main role="main">
      @section('body')
        @visual_layout_content
      @show
    </main>

    @stack('scripts')
  </body>

</html>

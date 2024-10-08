@php($direction = core()->getCurrentLocale()->direction)

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $direction }}">

  <head>
    @include('shop::partials.head')
  </head>

  <body class="{{ $direction }} style="scroll-behavior: smooth;">

    <main role="main" tabindex="-1">
      @section('body')
        @visual_layout_content
      @show
    </main>

    @include('shop::partials.scripts')
  </body>

</html>

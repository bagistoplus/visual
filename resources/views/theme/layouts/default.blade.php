@php($direction = core()->getCurrentLocale()->direction)

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $direction }}">

  <head>
    @include('shop::partials.head')
  </head>

  <body class="{{ $direction }} style="scroll-behavior: smooth;">
    @foreach ($theme->settings->whereStartsWith('color_') as $color)
      @dump($color)
    @endforeach
    <main role="main" tabindex="-1">
      <visual:section name="visual-announcement-bar" />
      @section('body')
        @visual_layout_content
      @show
      <visual:section name="visual-announcement-bar" />
    </main>

    @include('shop::partials.scripts')
    @livewireScripts
  </body>

</html>

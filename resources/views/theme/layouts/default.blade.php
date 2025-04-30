@php
  $direction = core()->getCurrentLocale()->direction;
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $direction }}">

  <head>
    {!! view_render_event('bagisto.shop.layout.head.before') !!}

    @include('shop::partials.head')

    {!! view_render_event('bagisto.shop.layout.head.after') !!}
  </head>

  <body class="{{ $direction }} style="scroll-behavior: smooth;">
    {!! view_render_event('bagisto.shop.layout.body.before') !!}

    <x-shop::toasts />

    <x-shop::confirm-modal />

    {!! view_render_event('bagisto.shop.layout.content.before') !!}

    <main role="main" tabindex="-1">
      <visual:section name="visual-announcement-bar" />
      <visual:section name="visual-header" />
      <div class="flex justify-center py-10">
        <button
          class="border-primary hover:bg-primary-500/5 active:bg-primary ring-primary/80 text-primary rounded-lg border bg-transparent px-4 py-3 ring-2 ring-offset-2">Primary</button>
      </div>
      @section('body')
        @visual_layout_content
        @foreach ($theme->settings->color_schemes as $scheme)
          {{ $scheme->tokens() }}
          {{--
            --color-background: oklch();
            --color-primary: oklch();
            --color-on-primary: oklch();
            --color-primary-50: oklch()
          --}}
        @endforeach
      @show

      <visual:section name="visual-footer" />
    </main>

    {!! view_render_event('bagisto.shop.layout.content.after') !!}

    {!! view_render_event('bagisto.shop.layout.body.after') !!}

    @stack('scripts')
    @livewireScriptConfig
  </body>

</html>

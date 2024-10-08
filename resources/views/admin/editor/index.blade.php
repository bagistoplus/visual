<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

  <head>
    <title>Visual Editor</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if ($favicon = core()->getConfigData('general.design.admin_logo.favicon', core()->getCurrentChannelCode()))
      <link rel="icon" sizes="16x16" href="{{ \Illuminate\Support\Facades\Storage::url($favicon) }}" />
    @else
      <link rel="icon" sizes="16x16" href="{{ asset('vendor/webkul/ui/assets/images/favicon.ico') }}" />
    @endif

    <script type="text/javascript">
      window.ThemeEditor = {
        baseUrl: "{{ $baseUrl }}",
        storefrontUrl: "{{ $storefrontUrl }}",
        channels: @json($channels),
        defaultChannel: "{{ $defaultChannel }}",
        routes: @json($routes)
      }
    </script>

    {{-- blade-formatter-disable --}}
    {{
      Vite::useHotFile('vendor/bagistoplus/visual/editor.hot')
        ->useBuildDirectory('vendor/bagistoplus/visual/editor')
        ->withEntryPoints(['resources/assets/editor/index.ts'])
    }}
    {{-- blade-formatter-enable --}}
  </head>

  <body @if (core()->getCurrentLocale()->direction == 'rtl') class="rtl" @endif style="scroll-behavior: smooth;">

    <div id="app"></div>

  </body>

</html>

<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

  <head>
    <title>Visual Editor</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if ($favicon = core()->getConfigData('general.design.admin_logo.favicon', core()->getCurrentChannelCode()))
      <link
        rel="icon"
        sizes="16x16"
        href="{{ \Illuminate\Support\Facades\Storage::url($favicon) }}"
      />
    @else
      <link
        rel="icon"
        sizes="16x16"
        href="{{ bagisto_asset('images/favicon.ico') }}"
      />
    @endif

    {{ ThemeEditor::renderStyles() }}

    <script type="text/javascript">
      window.editorConfig = @json($config);
    </script>

    {{-- blade-formatter-disable --}}
    {{
      Vite::useHotFile('vendor/bagistoplus/visual/editor.hot')
        ->useBuildDirectory('vendor/bagistoplus/visual/editor')
        ->withEntryPoints(['resources/assets/editor/index.ts'])
    }}
    {{-- blade-formatter-enable --}}

    {{ ThemeEditor::renderScripts() }}
  </head>

  <body @if (core()->getCurrentLocale()->direction == 'rtl') class="rtl" @endif style="margin:0; padding: 0; position: relative">

    <div id="app" style="width: 100vw; height: 100vh; position: absolute; top: 0; left: 0;"></div>

  </body>

</html>

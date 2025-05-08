@if (ThemeEditor::inDesignMode())
  <style type="text/css">
    #section-overlay {
      border: 2px solid #0041ff;
      position: absolute;
      width: 0;
      height: 0;
      display: none;
      pointer-events: none;
      text-align: center;
      padding-top: 4px;
      z-index: 9999;
    }

    #buttons {
      position: absolute;
      top: 100%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: #0041ff;
      padding: 8px;
      border-radius: 4px;
      display: none;
      gap: 8px;
    }

    #section-overlay button {
      pointer-events: auto;
      color: #fff;
      padding: 4px;
      border-radius: 4px;
    }

    #section-overlay button:hover {
      background-color: #151f8c;
    }

    #move-up,
    #move-down {
      display: none;
    }

    #section-overlay button svg {
      width: 20px;
    }

    #label {
      position: absolute;
      top: 0;
      left: 2px;
      background-color: #0041ff;
      color: white;
      border-radius: 0 0 4px 4px;
      padding: 0 4px;
      font-size: 0.8rem;
    }
  </style>

  <div id="section-overlay">
    <span id="label"></span>
    <div id="buttons">
      <button id="move-down" title="Move down"><x-lucide-arrow-down /></button>
      <button
        id="move-up"
        title="Move up"
        style="margin-right: 8px;"
      ><x-lucide-arrow-up /></button>
      <button id="edit" title="Edit"><x-lucide-pencil /></button>
      <button
        id="disable"
        title="Hide"
        style="margin-right: 8px;"
      ><x-lucide-eye-off /></button>
      <button id="remove" title="Remove"><x-lucide-trash-2 /></button>
    </div>
  </div>
@endif

<script type="text/javascript">
  window.Visual = {
    inPreviewMode: true,
    inDesignMode: false,
    theme: @json($theme)
  }
</script>

@if (ThemeEditor::inDesignMode())
  <script type="text/javascript">
    window.themeData = @json($themeData);
    window.settingsSchema = @json($settingsSchema);
    window.templates = @json($templates);
  </script>
  {{-- blade-formatter-disable --}}
  {{
    Vite::useHotFile('vendor/bagistoplus/visual/editor.hot')
      ->useBuildDirectory('vendor/bagistoplus/visual/editor')
      ->withEntryPoints(['resources/assets/editor/injected.ts'])
  }}
{{-- blade-formatter-enable --}}
@endif

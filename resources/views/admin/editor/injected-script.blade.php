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
      display: flex;
      gap: 8px;
    }

    #section-overlay .btn {
      pointer-events: auto;
      color: #fff;
      padding: 4px;
      border-radius: 4px;
    }

    #section-overlay .btn:hover {
      background-color: #151f8c;
    }

    .move-up,
    .move-down {
      display: none;
    }

    #section-overlay .btn svg {
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
    <span id="label">section name</span>
    <div id="buttons">
      <button
        id="move-down"
        class="btn move-down"
        title="Move down"
      ><x-heroicon-o-arrow-down /></button>
      <button
        id="move-up"
        class="btn move-up"
        title="Move up"
        style="margin-right: 8px;"
      ><x-heroicon-o-arrow-up /></button>
      <button
        id="edit"
        class="btn"
        title="Edit"
      ><x-heroicon-o-pencil /></button>
      <button
        id="disable"
        class="btn"
        title="Hide"
        style="margin-right: 8px;"
      ><x-heroicon-o-eye-slash /></button>
      <button
        id="remove"
        class="btn"
        title="Remove"
      ><x-heroicon-o-trash /></button>
    </div>
  </div>

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

<script type="text/javascript">
  // if (window.Livewire) {
  //   window.Livewire.addHeaders({
  //     'x-visual-editor-theme': '{{ $theme }}'
  //   });
  // }
</script>

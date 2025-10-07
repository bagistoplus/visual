<!-- Page Data for Craftile Editor -->
<script type="application/json" id="page-data">
  @json($pageData)
</script>

{{-- blade-formatter-disable --}}
  {{
    Vite::useHotFile('vendor/bagistoplus/visual/editor.hot')
      ->useBuildDirectory('vendor/bagistoplus/visual/editor')
      ->withEntryPoints(['resources/assets/editor/injected.ts'])
  }}
{{-- blade-formatter-enable --}}

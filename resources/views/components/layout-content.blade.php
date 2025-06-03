@php
  if (ThemeEditor::inDesignMode()) {
      ThemeEditor::startRenderingTemplate();
  }
@endphp
<!--BEGIN: template-->
{{ $slot }}
<!--END: template-->
@php
  if (ThemeEditor::inDesignMode()) {
      ThemeEditor::stopRenderingTemplate();
  }
@endphp

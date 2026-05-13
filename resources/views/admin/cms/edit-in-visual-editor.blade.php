@if (!empty($url))
  <a
    id="visual-cms-edit-button"
    href="{{ $url }}"
    class="secondary-button"
    style="display: none;"
  >
    {{ __('visual::admin.cms.edit-in-visual-editor') }}
  </a>

  @pushOnce('scripts')
    <script>
      (() => {
        const moveButton = () => {
          const button = document.getElementById('visual-cms-edit-button');
          const form = button?.closest('form');
          const saveButton = form?.querySelector('button[type="submit"].primary-button');

          if (!button || !saveButton?.parentElement) {
            return false;
          }

          saveButton.parentElement.insertBefore(button, saveButton);
          button.style.display = '';

          return true;
        };

        const scheduleMove = (attempt = 0) => {
          console.log('Attempting to move button', {
            attempt
          });
          if (moveButton() || attempt >= 50) {
            return;
          }

          window.setTimeout(() => scheduleMove(attempt + 1), 50);
        };

        setTimeout(() => scheduleMove(), 0);
      })
      ();
    </script>
  @endpushOnce
@endif

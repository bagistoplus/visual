@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.store('confirm').setDefaults({
        title: "@lang('shop::app.components.modal.confirm.title')",
        description: "@lang('shop::app.components.modal.confirm.message')",
        confirmText: "@lang('shop::app.components.modal.confirm.agree-btn')",
        cancelText: "@lang('shop::app.components.modal.confirm.disagree-btn')",
      });
    });
  </script>
@endpushOnce

<x-shop::ui.modal name="confirm">
  <x-shop::ui.modal.title x-text="$store.confirm.title" />

  <div class="mt-4 text-neutral-600">
    <x-shop::ui.modal.description x-text="$store.confirm.description" />
  </div>

  <div class="mt-6 flex justify-end gap-2">
    <x-shop::ui.modal.close>
      <x-shop::ui.button variant="soft" color="secondary">
        <span x-text="$store.confirm.cancelText"></span>
      </x-shop::ui.button>
    </x-shop::ui.modal.close>

    <x-shop::ui.modal.close>
      <x-shop::ui.button color="primary" x-on:click="$store.confirm.confirm()">
        <span x-text="$store.confirm.confirmText"></span>
      </x-shop::ui.button>
    </x-shop::ui.modal.close>
  </div>
</x-shop::ui.modal>

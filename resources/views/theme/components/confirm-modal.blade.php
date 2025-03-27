<x-shop::ui.modal name="confirm">
  <h2 x-text="$store.confirmModal.title" class="text-lg font-semibold text-neutral-700"></h2>

  <x-shop::ui.button
    icon="lucide-x"
    icon-only
    rounded
    variant="ghost"
    color="secondary"
    size="sm"
    class="!absolute -right-2 -top-2"
    x-on:click="$modal.hide()"
  />

  <div class="mt-2 text-neutral-600">
    <p x-text="$store.confirmModal.message"></p>
  </div>

  <div class="mt-6 flex justify-end gap-2">
    <x-shop::ui.button
      variant="soft"
      color="secondary"
      x-on:click="$modal.hide()"
    >
      <span x-text="$store.confirmModal.cancelText"></span>
    </x-shop::ui.button>

    <x-shop::ui.button color="primary" x-on:click="$store.confirmModal.accept()">
      <span x-text="$store.confirmModal.okText"></span>
    </x-shop::ui.button>
  </div>
</x-shop::ui.modal>

<x-shop::ui.dropdown>
  <x-slot:trigger>
    <x-shop::ui.button
      type="button"
      color="neutral"
      variant="outline"
      size="sm"
    >
      <span class="inline-flex items-center">
        @lang('shop::app.components.datagrid.toolbar.mass-actions.select-action')
        <x-lucide-chevron-down class="ml-2 h-4" />
      </span>
    </x-shop::ui.button>
  </x-slot>
  <div class="bg-background mt-1 w-40 rounded-lg border border-neutral-200 p-1">
    <template x-for="(massAction, index) in available.massActions">
      <div class="contents" x-datagrid:mass-action="index">
        <template x-if="!$massAction.hasOptions">
          <a
            href="#"
            class="relative flex cursor-default select-none items-center rounded px-2 py-1.5 text-sm outline-none transition-colors hover:bg-neutral-100"
            x-on:click.prevent="close(); $massAction.performAction();"
          >
            <span x-text="massAction.title"></span>
          </a>
        </template>
        <template x-if="$massAction.hasOptions">
          <div class="group relative">
            <div class="flex cursor-default select-none items-center rounded px-2 py-1.5 text-sm outline-none transition-colors hover:bg-neutral-100">
              <span x-text="massAction.title"></span>
              <x-lucide-chevron-right class="ml-auto h-4 w-4" />
            </div>
            <div data-submenu
              class="invisible absolute right-0 top-0 mr-1 translate-x-full opacity-0 duration-200 ease-out group-hover:visible group-hover:mr-0 group-hover:opacity-100"
            >
              <div class="bg-background z-50 w-40 min-w-[8rem] overflow-hidden rounded-md border p-1 shadow-md">
                <template x-for="(option, optionIndex) in massAction.options">
                  <a
                    href="#"
                    class="relative flex cursor-default select-none items-center rounded px-2 py-1.5 text-sm outline-none transition-colors hover:bg-neutral-100"
                    x-on:click.prevent="close()"
                    x-datagrid:mass-action-option="optionIndex"
                  >
                    <span x-text="option.label"></span>
                  </a>
                </template>
              </div>
            </div>
          </div>
        </template>
      </div>
    </template>
  </div>
</x-shop::ui.dropdown>

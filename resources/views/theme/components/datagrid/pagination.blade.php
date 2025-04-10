<div x-datagrid:pagination class="flex items-center justify-between border-t border-neutral-100 p-4">
  <p class="text-xs font-medium" x-text="paginationText"></p>
  <nav aria-label="@lang('shop::app.components.datagrid.table.page-navigation')" role="navigation">
    <div class="inline-flex items-center rounded-lg border border-neutral-100">
      <button
        class="p-1.5 text-neutral-700 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:text-neutral-200 disabled:hover:bg-transparent lg:p-2"
        x-bind:disabled="$pagination.isFirstPage"
        x-on:click="$pagination.goToPreviousPage()"
      >
        <x-lucide-chevron-left class="h-4 w-4" />
      </button>

      <template x-for="page in available.meta.last_page">
        <button
          class="data-[active]:text-primary border-l border-neutral-100 px-2 py-1.5 text-sm text-neutral-700 hover:bg-neutral-50 lg:px-3"
          x-bind:data-active="$pagination.currentPage === page"
          x-on:click="$pagination.goToPage(page)"
        >
          <span x-text="page"></span>
        </button>
      </template>

      <button
        class="border-l border-neutral-100 p-1.5 text-neutral-700 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:text-neutral-200 disabled:hover:bg-transparent lg:p-2"
        x-bind:disabled="$pagination.isLastPage"
        x-on:click="$pagination.goToNextPage()"
      >
        <x-lucide-chevron-right class="h-4 w-4" />
      </button>
    </div>
  </nav>
</div>

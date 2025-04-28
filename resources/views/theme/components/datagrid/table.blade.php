<table class="w-full">
  <thead class="max-sm:hidden">
    <tr class="bg-surface-alt border-b border-neutral-200">
      <template x-if="haveMassActions > 0">
        <th class="px-6 py-4 text-left">
          <input type="checkbox" x-datagrid:select-all>
        </th>
      </template>

      <template x-for="column in available.columns">
        <template x-if="column.visibility">
          <th x-datagrid:table-header="column.index" class="whitespace-nowrap px-6 py-4 text-left align-middle text-sm font-medium text-neutral-700">
            <span x-text="column.label"></span>
            <template x-if="$tableHeader.isSorted">
              <x-lucide-chevron-down class="inline h-4 w-4" x-bind:class="{ 'transform rotate-180': $tableHeader.sortOrder !== 'asc' }" />
            </template>
          </th>
        </template>
      </template>

      <template x-if="available.actions.length > 0">
        <th class="px-6 py-4 align-middle text-sm font-medium text-neutral-700">
          @lang('shop::app.components.datagrid.table.actions')
        </th>
      </template>
    </tr>
  </thead>

  <tbody class="divide-neutral-200 sm:divide-y">
    <template x-if="haveRecords">
      <template x-for="record in available.records">
        <tr x-datagrid:table-row="record" class="hover:bg-neutral-50 max-sm:mb-4 max-sm:block max-sm:rounded max-sm:border">
          <template x-if="haveMassActions">
            <td class="px-6 py-4 text-left max-sm:block">
              <input x-datagrid:table-row-checkbox type="checkbox">
            </td>
          </template>

          <template x-for="column in available.columns">
            <template x-if="column.visibility">
              <td
                class="px-2 py-1 max-sm:flex max-sm:justify-between max-sm:before:pr-2 max-sm:before:text-sm max-sm:before:font-semibold max-sm:before:text-neutral-600 max-sm:before:content-[attr(data-label)] sm:px-6 sm:py-4"
                x-bind:data-label="column.label"
              >
                <span x-html="record[column.index]"></span>
              </td>
            </template>
          </template>

          <template x-if="available.actions.length > 0">
            <td
              class="px-2 py-1 max-sm:flex max-sm:justify-between max-sm:before:text-sm max-sm:before:font-semibold max-sm:before:text-neutral-600 max-sm:before:content-[attr(data-label)] sm:px-6 sm:py-4"
              data-label="@lang('shop::app.components.datagrid.table.actions')"
            >
              <div class="flex justify-center gap-2">
                <template x-for="action in record.actions">
                  <x-shop::ui.button
                    size="xs"
                    variant="ghost"
                    color="secondary"
                    x-on:click="handleAction(action)"
                  >
                    <span class="inline-block h-4 w-4" x-bind:class="action.icon"></span>
                    <span x-text="!action.icon ? action.title : ''"></span>
                  </x-shop::ui.button>
                </template>
              </div>
            </td>
          </template>
        </tr>
      </template>
    </template>
  </tbody>
</table>

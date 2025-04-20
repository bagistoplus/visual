<x-shop::ui.drawer {{ $attributes }} :title="trans('shop::app.components.datagrid.toolbar.filter.apply-filter')">
  <x-slot:trigger>
    <x-shop::ui.button
      icon="lucide-filter"
      color="neutral"
      variant="outline"
      size="sm"
    >
      @lang('shop::app.components.datagrid.toolbar.filter.title')
    </x-shop::ui.button>
  </x-slot:trigger>

  <div class="h-full space-y-4 overflow-y-auto border-t p-4">
    <template x-for="column in available.columns">
      <template x-if="column.filterable">
        <div x-datagrid:filter="column.index">
          <!-- Filter Label and Clear All Button -->
          <div class="mb-1 flex items-center justify-between">
            <label class="text-sm font-medium text-neutral-600" x-html="column.label"></label>
            <x-shop::ui.button
              variant="link"
              size="xs"
              x-datagrid:clear-filter=""
            >
              @lang('shop::app.components.datagrid.toolbar.filter.custom-filters.clear-all')
            </x-shop::ui.button>
          </div>

          <!-- Date Filter -->
          <template x-if="column.type === 'date'">
            <div>
              <template x-if="column.filterable_type === 'date_range'">
                <div class="grid grid-cols-2 gap-1.5">
                  <template x-for="option in column.filterable_options">
                    <x-shop::ui.button
                      variant="outline"
                      color="neutral"
                      size="sm"
                      x-on:click="$filter.addQuickOptionValue(option)"
                    >
                      <span x-text="option.label"></span>
                    </x-shop::ui.button>
                  </template>
                  <x-shop::ui.form.input
                    type="date"
                    size="sm"
                    x-bind:placeholder="column.label"
                    x-on:change="$filter.addDateRangeValue($event.target.value, 'from')"
                  />
                  <x-shop::ui.form.input
                    type="date"
                    size="sm"
                    x-bind:placeholder="column.label"
                    x-on:change="$filter.addDateRangeValue($event.target.value, 'to')"
                  />
                </div>
              </template>

              <template x-if="column.filterable_type !== 'date_range'">
                <x-shop::ui.form.input
                  type="date"
                  size="sm"
                  x-bind:placeholder="column.label"
                  x-on:change="$filter.addValue($event.target.value)"
                />
              </template>
            </div>
          </template>

          <!-- DateTime Filter -->
          <template x-if="column.type === 'datetime'">
            <div>
              <template x-if="column.filterable_type === 'datetime_range'">
                <div class="grid grid-cols-2 gap-1.5">
                  <template x-for="option in column.filterable_options">
                    <x-shop::ui.button
                      variant="outline"
                      color="neutral"
                      size="sm"
                      x-on:click="$filter.addQuickOptionValue(option)"
                    >
                      <span x-text="option.label"></span>
                    </x-shop::ui.button>
                  </template>
                  <x-shop::ui.form.input
                    type="datetime-local"
                    size="sm"
                    x-bind:placeholder="column.label"
                    x-on:change="$filter.addDateRangeValue($event.target.value, 'from')"
                  />
                  <x-shop::ui.form.input
                    type="datetime-local"
                    size="sm"
                    x-bind:placeholder="column.label"
                    x-on:change="$filter.addDateRangeValue($event.target.value, 'to')"
                  />
                </div>
              </template>

              <template x-if="column.filterable_type !== 'datetime_range'">
                <x-shop::ui.form.input
                  type="datetime-local"
                  size="sm"
                  x-bind:placeholder="column.label"
                  x-on:change="$filter.addValue($event.target.value)"
                />
              </template>
            </div>
          </template>

          <!-- Other Filter Types -->
          <template x-if="!['date', 'datetime'].includes(column.type)">
            <div>
              <template x-if="column.filterable_type === 'dropdown'">
                <x-shop::ui.form.select size="sm" x-on:change="$filter.addValue($event.target.value)">
                  <option
                    value=""
                    disabled
                    x-bind:selected="$filter.appliedValues.length === 0"
                  >
                    @lang('shop::app.components.datagrid.toolbar.filter.dropdown.select')
                  </option>
                  <template x-for="option in column.filterable_options">
                    <option
                      x-text="option.label"
                      x-bind:value="option.value"
                      x-bind:selected="$filter.appliedValues.includes(option.value)"
                    ></option>
                  </template>
                </x-shop::ui.form.select>
              </template>

              <template x-if="column.filterable_type !== 'dropdown'">
                <x-shop::ui.form.input
                  size="sm"
                  x-bind:placeholder="column.label"
                  x-on:blur="$filter.addValue($event.target.value); $event.target.value = ''"
                  x-on:keyup.enter="$filter.addValue($event.target.value); $event.target.value = ''"
                />
              </template>
            </div>
          </template>

          <!-- Display Applied Filter Values -->
          <div class="mt-2 flex flex-wrap gap-2">
            <template x-for="value in $filter.appliedValues" :key="value.toString()">
              <div x-datagrid:filter-value="value" class="bg-secondary text-secondary-50 inline-flex items-center gap-2 rounded-lg border px-2 py-0.5 text-sm">
                <span x-text="$filterValue.label"></span>
                <button class="hover:bg-secondary-400 rounded-full p-px" x-on:click="$filterValue.remove()">
                  <x-lucide-x class="h-3 w-3" />
                </button>
              </div>
            </template>
          </div>
        </div>
      </template>
    </template>

    <div>
      <x-shop::ui.button
        class="w-full"
        x-on:click="$modal.hide()"
        x-datagrid:apply-filters=""
      >
        @lang('admin::app.components.datagrid.toolbar.filter.apply-filters-btn')
      </x-shop::ui.button>
    </div>
  </div>
</x-shop::ui.drawer>

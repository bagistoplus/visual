@props(['src' => ''])
@pushOnce('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('VisualDataGrid', (src) => ({
        // --------------------------
        // State Initialization
        // --------------------------
        src,
        isLoading: false,
        isFilterDirty: false,

        messages: {
          pagination: {
            showing: "@lang('shop::app.components.datagrid.table.showing')",
            to: "@lang('shop::app.components.datagrid.table.to')",
            of: "@lang('shop::app.components.datagrid.table.of')"
          }
        },

        available: {
          id: null,
          columns: [],
          actions: [],
          massActions: [],
          records: [],
          meta: {}
        },

        applied: {
          massActions: {
            meta: {
              mode: 'none',
              action: null
            },
            indices: [],
            value: null,
          },
          pagination: {
            page: 1,
            perPage: 10
          },
          sort: {
            column: null,
            order: null
          },
          filters: {
            columns: [{
              index: 'all',
              value: []
            }]
          },
        },

        // --------------------------
        // Bindings for UI Elements
        // --------------------------
        bindings: {
          search: {
            ['x-bind:value']() {
              return this.searchedValue;
            },
            ['@keyup.enter.prevent']($event) {
              this.updateGlobalSearch($event.target.value);
            }
          },
          selectAll: {
            ['x-bind:checked']() {
              return ['all'].includes(this.applied.massActions.meta.mode);
            },
            ['@change']() {
              this.toggleSelectAll();
            }
          },
          pagination: {
            ['x-bind:value']() {
              return this.applied.pagination.perPage;
            },
            ['x-on:change']($event) {
              this.changePagination($event.target.value);
            }
          }
        },

        // --------------------------
        // Computed Properties
        // --------------------------
        get searchedValue() {
          const searchColumn = this.findAppliedColumn('all');
          return searchColumn?.value ?? [];
        },

        get paginationText() {
          return [
            this.messages.pagination.showing.replace(':firstItem', this.available.meta.from),
            this.messages.pagination.to.replace(':lastItem', this.available.meta.to),
            this.messages.pagination.of.replace(':total', this.available.meta.total),
          ].join(' ');
        },

        // --------------------------
        // Initialization
        // --------------------------
        init() {
          this.loadSavedState();
          this.applyUrlParams();
          this.fetchData();
          this.setupWatchers();
        },
        setupWatchers() {
          this.$watch('applied.massActions.indices', () => {
            this.setCurrentSelectionMode();
          });
          this.$watch('available.records', () => {
            this.setCurrentSelectionMode();
            this.updateDatagrids();
            this.notifyExportComponent();
          });
        },

        // --------------------------
        // Local Storage & URL Params
        // --------------------------
        loadSavedState() {
          const datagrids = this.getFromStorage('datagrids') || [];
          const url = this.src.split('?')[0];
          const savedGrid = datagrids.find(grid => grid.src === url);
          if (savedGrid) {
            Object.assign(this.applied, {
              pagination: savedGrid.applied.pagination,
              sort: savedGrid.applied.sort,
              filters: savedGrid.applied.filters
            });
          }
        },
        applyUrlParams() {
          const urlParams = new URLSearchParams(window.location.search);
          if (urlParams.has('search')) {
            const searchColumn = this.findAppliedColumn('all');
            if (searchColumn) {
              searchColumn.value = [urlParams.get('search')];
            }
          }
        },

        // --------------------------
        // Filter Utilities
        // --------------------------
        findAppliedColumn(index) {
          return this.applied.filters.columns.find(column => column.index === index);
        },
        hasAnyAppliedColumnValues(index) {
          const appliedColumn = this.findAppliedColumn(index);
          return appliedColumn ? this.hasAnyValue(appliedColumn) : false;
        },
        hasAnyValue(column) {
          return column.allow_multiple_values ? (column.value.length > 0) : !!column.value;
        },
        getAppliedColumnValues(columnIndex) {
          const appliedColumn = this.findAppliedColumn(columnIndex);
          if (appliedColumn) {
            if (appliedColumn.allow_multiple_values && !['date', 'datetime'].includes(appliedColumn.type)) {
              return appliedColumn.value ?? [];
            }
            return appliedColumn.value ? (Array.isArray(appliedColumn.value) ? appliedColumn.value : [appliedColumn.value]) : [];
          }
          return [];
        },
        removeAppliedColumnValue(columnIndex, value) {
          const appliedColumn = this.findAppliedColumn(columnIndex);
          if (!appliedColumn) return;
          if (['date', 'datetime'].includes(appliedColumn.type)) {
            appliedColumn.value = [];
          } else if (appliedColumn.allow_multiple_values) {
            appliedColumn.value = appliedColumn.value.filter(v => v !== value);
          } else {
            appliedColumn.value = '';
          }
          if (!appliedColumn.value?.length) {
            this.applied.filters.columns = this.applied.filters.columns.filter(column => column.index !== columnIndex);
          }
          this.isFilterDirty = true;
        },
        removeAppliedColumnAllValues(columnIndex) {
          this.applied.filters.columns = this.applied.filters.columns.filter(column => column.index !== columnIndex);
          this.isFilterDirty = true;
        },
        getColumnValueLabel(column, value) {
          if (column.filterable_options.length > 0) {
            const option = column.filterable_options.find(option => {
              if (['date', 'datetime', 'date_range', 'datetime_range'].includes(column.filterable_type)) {
                return option.name === value;
              }
              if (column.filterable_type === 'dropdown') {
                return option.value === value;
              }
              return false;
            });
            return option?.label ?? value;
          }
          return value;
        },

        // --------------------------
        // Filter & Search Handlers
        // --------------------------
        updateGlobalSearch(value) {
          let searchColumn = this.findAppliedColumn('all');
          if (searchColumn) {
            searchColumn.value = value ? [value] : [];
          } else if (value) {
            this.applied.filters.columns.push({
              index: 'all',
              value: [value]
            });
          }
          this.applied.pagination.page = 1;
          this.fetchData();
        },
        addFilter(value, column = null, additional = {}) {
          if (additional?.quickFilter?.isActive && ['date', 'datetime'].includes(column?.type)) {
            this.applyColumnValues(column, additional.quickFilter.selectedFilter.name);
            return;
          }
          this.applyColumnValues(column, value, additional);
        },
        applyColumnValues(column, requestedValue, additional = {}) {
          if (!column) return;
          const appliedColumn = this.findAppliedColumn(column.index);
          if (this.shouldSkipValue(appliedColumn, requestedValue)) return;
          if (['date', 'datetime'].includes(column.type)) {
            this.handleDateFilter(column, appliedColumn, requestedValue, additional.range);
          } else {
            this.updateOrCreateColumn(column, appliedColumn, requestedValue);
          }
          this.isFilterDirty = true;
        },
        handleDateFilter(column, appliedColumn, value, range) {
          if (!range) {
            this.updateOrCreateColumn(column, appliedColumn, value);
            return;
          }

          let rangeValues = ['', ''];
          if (appliedColumn && typeof appliedColumn.value !== 'string') {
            rangeValues = [...appliedColumn.value[0]];
          }

          if (range.name === 'from') rangeValues[0] = value;
          if (range.name === 'to') rangeValues[1] = value;

          this.updateOrCreateColumn(column, appliedColumn, [rangeValues]);
        },

        updateOrCreateColumn(column, appliedColumn, value) {
          const isDateType = ['date', 'datetime'].includes(column.type);
          const shouldBeArray = !isDateType && column.allow_multiple_values;

          if (appliedColumn) {
            if (shouldBeArray && !Array.isArray(value)) {
              appliedColumn.value.push(value);
            } else {
              appliedColumn.value = value;
            }
          } else {
            const formattedValue = shouldBeArray && !Array.isArray(value) ? [value] : value;

            this.applied.filters.columns.push({
              index: column.index,
              label: column.label,
              type: column.type,
              value: formattedValue,
              allow_multiple_values: column.allow_multiple_values,
            });
          }
        },

        shouldSkipValue(appliedColumn, requestedValue) {
          return (
            requestedValue === undefined ||
            requestedValue === '' ||
            (appliedColumn?.allow_multiple_values && appliedColumn?.value.includes(requestedValue)) ||
            (!appliedColumn?.allow_multiple_values && appliedColumn?.value === requestedValue)
          );
        },

        applyFilters() {
          this.applied.pagination.page = 1;
          this.fetchData();
        },

        // --------------------------
        // Sorting
        // --------------------------
        changeSort(column) {
          if (!column.sortable) return;

          this.applied.sort = {
            column: column.index,
            order: this.applied.sort.column === column.index && this.applied.sort.order === 'asc' ? 'desc' : 'asc'
          };

          this.applied.pagination.page = 1;
          this.fetchData();
        },

        // --------------------------
        // Pagination Handler
        // --------------------------
        changePagination(perPage) {
          this.applied.pagination.perPage = perPage;
          if (this.available.meta.last_page <= this.applied.pagination.page) {
            this.applied.pagination.page = 1;
          }
          this.fetchData();
        },

        changeToPreviousPage() {
          this.changePage(this.applied.pagination.page - 1);
        },

        changeToNextPage() {
          this.changePage(this.applied.pagination.page + 1);
        },

        changePage(page) {
          if (page === this.applied.pagination.page) {
            return;
          }

          this.applied.pagination.page = page;
          this.fetchData();
        },

        // --------------------------
        // Data Fetching
        // --------------------------
        fetchData() {
          this.isLoading = true;
          this.$request(this.src, 'GET', this.buildRequestParams())
            .then(data => {
              Object.assign(this.available, {
                id: data.id,
                columns: data.columns,
                actions: data.actions,
                massActions: data.mass_actions,
                records: data.records,
                meta: data.meta
              });
              this.isLoading = false;
            })
            .catch(() => {
              this.isLoading = false;
            });
        },

        buildRequestParams() {
          const params = {
            pagination: {
              page: this.applied.pagination.page,
              per_page: this.applied.pagination.perPage,
            },
            sort: {},
            filters: {}
          };

          if (this.applied.sort.column && this.applied.sort.order) {
            params.sort = this.applied.sort;
          }

          this.applied.filters.columns.forEach(column => {
            params.filters[column.index] = column.value;
          });

          return params;
        },

        // --------------------------
        // Mass Actions (Bulk Operations)
        // --------------------------
        toggleSelectAll() {
          const primaryColumn = this.available.meta.primary_column;
          const currentMode = this.applied.massActions.meta.mode;
          const currentIds = this.available.records.map(record => record[primaryColumn]);

          if (currentMode === 'none') {
            this.applied.massActions.indices = [
              ...this.applied.massActions.indices,
              ...currentIds
            ];
            this.applied.massActions.meta.mode = 'all';
          } else {
            this.applied.massActions.indices = this.applied.massActions.indices.filter(id => !currentIds.includes(id));
            this.applied.massActions.meta.mode = 'none';
          }
        },

        setCurrentSelectionMode() {
          this.applied.massActions.meta.mode = 'none';

          if (this.available.records.length === 0) return;

          const primaryColumn = this.available.meta.primary_column;
          const selectedCount = this.available.records.filter(record =>
            this.applied.massActions.indices.includes(record[primaryColumn])
          ).length;

          if (selectedCount > 0) {
            this.applied.massActions.meta.mode =
              selectedCount === this.available.records.length ? 'all' : 'partial';
          }
        },

        handleAction(action) {
          const method = action.method.toLowerCase();

          if (['post', 'put', 'patch', 'delete'].includes(method)) {
            if (window.confirm('Are you sure you want to perform this action?')) {
              this.$request(action.url, method)
                .then(data => {
                  this.$toast({
                    type: 'success',
                    message: data.message
                  });
                })
                .catch(error => {
                  if (error.message) {
                    this.$toast({
                      type: 'error',
                      message: error.message
                    });
                  }
                })
                .finally(() => this.fetchData());
            }
          } else if (method === 'get') {
            window.location.href = action.url;
          } else {
            console.error('Method not supported.');
          }
        },

        handleMassAction(action, option = null) {
          this.applied.massActions.meta.action = action;

          if (option) {
            this.applied.massActions.value = option.value;
          }

          if (!this.validateMassAction()) return;

          const method = action.method.toLowerCase();

          if (window.confirm('Are you sure you want to perform this action?')) {
            if (['post', 'put', 'patch', 'delete'].includes(method)) {
              const params = {
                indices: this.applied.massActions.indices
              };

              if (method !== 'delete') {
                params['value'] = this.applied.massActions.value;
              }

              this.$request(action.url, method, params)
                .then(data => {
                  this.$toast({
                    type: 'success',
                    message: data.message
                  });
                })
                .catch(error => {
                  if (error.message) {
                    this.$toast({
                      type: 'error',
                      message: error.message
                    });
                  }
                })
                .finally(() => this.fetchData());
            } else {
              console.error('Method not supported.');
            }
          }
        },

        validateMassAction() {
          if (!this.applied.massActions.indices.length) {
            this.$toast({
              type: 'warning',
              message: "@lang('shop::app.components.datagrid.toolbar.mass-actions.no-records-selected')"
            });
            return false;
          }

          if (!this.applied.massActions.meta.action) {
            this.$toast({
              type: 'warning',
              message: "@lang('shop::app.components.datagrid.toolbar.mass-actions.must-select-a-mass-action')"
            });
            return false;
          }

          if (
            this.applied.massActions.meta.action?.options?.length &&
            this.applied.massActions.value === null
          ) {
            this.$toast({
              type: 'warning',
              message: "@lang('shop::app.components.datagrid.toolbar.mass-actions.must-select-a-mass-action-option')"
            });
            return false;
          }

          return true;
        },

        // --------------------------
        // Storage & Notifications
        // --------------------------
        saveToStorage(key, value) {
          localStorage.setItem(key, JSON.stringify(value));
        },

        getFromStorage(key) {
          const value = localStorage.getItem(key);
          return value ? JSON.parse(value) : null;
        },

        updateDatagrids() {
          let datagrids = this.getFromStorage('datagrids') || [];
          const src = this.src.split('?')[0];
          const gridData = {
            src,
            requestCount: 0,
            available: this.available,
            applied: this.applied
          };
          const existingIndex = datagrids.findIndex(grid => grid.src === src);

          if (existingIndex >= 0) {
            gridData.requestCount = datagrids[existingIndex].requestCount + 1;
            datagrids[existingIndex] = gridData;
          } else {
            datagrids.push(gridData);
          }

          this.saveToStorage('datagrids', datagrids);
        },

        notifyExportComponent() {
          document.dispatchEvent(new CustomEvent('change-datagrid', {
            detail: {
              available: this.available,
              applied: this.applied
            }
          }));
        }
      }));
    });
  </script>
@endpushOnce

<!-- --------------------------
     Component Markup
--------------------------- -->
<div x-data="VisualDataGrid(@js($src))">
  <!-- Toolbar -->
  <div class="border-b border-neutral-200 p-3">
    <div class="flex flex-col gap-4 md:flex-row">
      <div class="flex flex-1 items-center justify-between gap-4">
        <!-- Mass Actions Dropdown (if records are selected) -->
        <template x-if="applied.massActions.indices.length > 0">
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
              <template x-for="massAction in available.massActions">
                <div class="contents">
                  <template x-if="massAction?.options?.length === 0">
                    <a
                      href="#"
                      class="relative flex cursor-default select-none items-center rounded px-2 py-1.5 text-sm outline-none transition-colors hover:bg-neutral-100"
                      x-on:click.prevent="handleMassAction(massAction); close()"
                    >
                      <span x-text="massAction.title"></span>
                    </a>
                  </template>
                  <template x-if="massAction?.options?.length > 0">
                    <div class="group relative">
                      <div class="flex cursor-default select-none items-center rounded px-2 py-1.5 text-sm outline-none transition-colors hover:bg-neutral-100">
                        <span x-text="massAction.title"></span>
                        <x-lucide-chevron-right class="ml-auto h-4 w-4" />
                      </div>
                      <div data-submenu class="invisible absolute right-0 top-0 mr-1 translate-x-full opacity-0 duration-200 ease-out group-hover:visible group-hover:mr-0 group-hover:opacity-100">
                        <div class="bg-background z-50 w-40 min-w-[8rem] overflow-hidden rounded-md border p-1 shadow-md">
                          <template x-for="option in massAction.options">
                            <a
                              href="#"
                              class="relative flex cursor-default select-none items-center rounded px-2 py-1.5 text-sm outline-none transition-colors hover:bg-neutral-100"
                              x-on:click.prevent="handleMassAction(massAction, option); close()"
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
            </x-shop::ui-dropdown>
        </template>

        <!-- Global Search Input (shown if no mass actions are active) -->
        <template x-if="applied.massActions.indices.length === 0">
          <x-shop::ui.form.input
            size="sm"
            type="search"
            prepend-icon="lucide-search"
            :placeholder="trans('shop::app.components.datagrid.toolbar.search.title')"
            x-bind="bindings.search"
          />
        </template>

        <p class="hidden md:block" x-text="'@lang('shop::app.components.datagrid.toolbar.results')'.replace(':total', available.meta.total ?? 0)"></p>

        <!-- Filter Slide-Over -->
        <x-shop::ui.slide-over class="ml-auto" :title="trans('shop::app.components.datagrid.toolbar.filter.apply-filter')">
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
          <div class="h-full w-80 space-y-4 overflow-y-auto p-4">
            <template x-for="column in available.columns">
              <template x-if="column.filterable">
                <div>
                  <!-- Filter Label and Clear All Button -->
                  <div class="mb-1 flex items-center justify-between">
                    <label class="text-sm font-medium text-neutral-600" x-html="column.label"></label>
                    <x-shop::ui.button
                      variant="link"
                      size="xs"
                      x-show="hasAnyAppliedColumnValues(column.index)"
                      x-on:click="removeAppliedColumnAllValues(column.index)"
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
                              x-on:click="addFilter(option.value, column, { quickFilter: { isActive: true, selectedFilter: option } })"
                            >
                              <span x-text="option.label"></span>
                            </x-shop::ui.button>
                          </template>
                          <x-shop::ui.form.input
                            type="date"
                            size="sm"
                            x-bind:placeholder="column.label"
                            x-on:change="addFilter($event.target.value, column, { range: { name: 'from', quickFilter: { isActive: false } } })"
                          />
                          <x-shop::ui.form.input
                            type="date"
                            size="sm"
                            x-bind:placeholder="column.label"
                            x-on:change="addFilter($event.target.value, column, { range: { name: 'to', quickFilter: { isActive: false } } })"
                          />
                        </div>
                      </template>
                      <template x-if="column.filterable_type !== 'date_range'">
                        <x-shop::ui.form.input
                          type="date"
                          size="sm"
                          x-bind:placeholder="column.label"
                          x-on:change="addFilter($event.target.value, column)"
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
                              x-on:click="addFilter(option.value, column, { quickFilter: { isActive: true, selectedFilter: option } })"
                            >
                              <span x-text="option.label"></span>
                            </x-shop::ui.button>
                          </template>
                          <x-shop::ui.form.input
                            type="datetime-local"
                            size="sm"
                            x-bind:placeholder="column.label"
                            x-on:change="addFilter($event.target.value, column, { range: { name: 'from', quickFilter: { isActive: false } } })"
                          />
                          <x-shop::ui.form.input
                            type="datetime-local"
                            size="sm"
                            x-bind:placeholder="column.label"
                            x-on:change="addFilter($event.target.value, column, { range: { name: 'to', quickFilter: { isActive: false } } })"
                          />
                        </div>
                      </template>
                      <template x-if="column.filterable_type !== 'datetime_range'">
                        <x-shop::ui.form.input
                          type="datetime-local"
                          size="sm"
                          x-bind:placeholder="column.label"
                          x-on:change="addFilter($event.target.value, column)"
                        />
                      </template>
                    </div>
                  </template>

                  <!-- Other Filter Types -->
                  <template x-if="!['date', 'datetime'].includes(column.type)">
                    <div>
                      <template x-if="column.filterable_type === 'dropdown'">
                        <x-shop::ui.form.select size="sm" x-on:change="addFilter($event.target.value, column)">
                          <option
                            value=""
                            disabled
                            x-bind:selected="getAppliedColumnValues(column.index).length === 0"
                          >
                            @lang('shop::app.components.datagrid.toolbar.filter.dropdown.select')
                          </option>
                          <template x-for="option in column.filterable_options">
                            <option
                              x-text="option.label"
                              x-bind:value="option.value"
                              x-bind:selected="getAppliedColumnValues(column.index).includes(option.value)"
                            ></option>
                          </template>
                        </x-shop::ui.form.select>
                      </template>
                      <template x-if="column.filterable_type !== 'dropdown'">
                        <x-shop::ui.form.input
                          size="sm"
                          x-bind:placeholder="column.label"
                          x-on:blur="addFilter($event.target.value, column); $event.target.value = ''"
                          x-on:keyup.enter="addFilter($event.target.value, column); $event.target.value = ''"
                        />
                      </template>
                    </div>
                  </template>

                  <!-- Display Applied Filter Values -->
                  <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="value in getAppliedColumnValues(column.index)">
                      <div class="bg-secondary text-secondary-50 inline-flex items-center gap-2 rounded-lg border px-2 py-0.5 text-sm">
                        <span x-text="getColumnValueLabel(column, value)"></span>
                        <button class="hover:bg-secondary-400 rounded-full p-px" x-on:click="removeAppliedColumnValue(column.index, value)">
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
                x-bind:disabled="!isFilterDirty"
                x-on:click="applyFilters"
              >
                @lang('admin::app.components.datagrid.toolbar.filter.apply-filters-btn')
              </x-shop::ui.button>
            </div>
          </div>
          </x-shop::ui-slide-over>
      </div>

      <!-- Pagination Controls (Toolbar Bottom) -->
      <div class="flex items-center justify-between gap-4">
        <x-shop::ui.form.select size="sm" x-bind="bindings.pagination">
          <template x-for="option in available.meta.per_page_options">
            <option x-bind:value="option" x-text="option"></option>
          </template>
        </x-shop::ui.form.select>
        <div class="md:hidden">7 results</div>
      </div>
    </div>
  </div>

  <!-- Table (hidden on mobile if mobile slot) -->
  <div class="@isset($mobile)hidden lg:block @endisset overflow-x-auto max-sm:p-2">
    <table class="w-full">
      <thead class="max-sm:hidden">
        <tr class="bg-surface-alt border-b border-neutral-200">
          <template x-if="available.massActions.length > 0">
            <th class="px-6 py-4 text-left">
              <input type="checkbox" x-bind="bindings.selectAll">
            </th>
          </template>
          <template x-for="column in available.columns">
            <template x-if="column.visibility">
              <th class="cursor-pointer px-6 py-4 text-left align-middle text-sm font-medium text-neutral-700" x-on:click="changeSort(column)">
                <span x-text="column.label"></span>
                <template x-if="column.index === applied.sort.column">
                  <x-lucide-chevron-down class="inline h-4 w-4" x-bind:class="applied.sort.order !== 'asc' ? 'transform rotate-180' : ''" />
                </template>
              </th>
            </template>
          </template>
          <template x-if="available.actions.length > 0">
            <th class="px-6 py-4 text-left align-middle text-sm font-medium text-neutral-700">
              @lang('shop::app.components.datagrid.table.actions')
            </th>
          </template>
        </tr>
      </thead>

      <tbody class="divide-neutral-200 sm:divide-y">
        <template x-if="available.records.length > 0">
          <template x-for="record in available.records">
            <tr class="hover:bg-gray-50 max-sm:mb-4 max-sm:block max-sm:rounded max-sm:border">
              <template x-if="available.massActions.length > 0">
                <td class="px-6 py-4 text-left max-sm:block">
                  <input
                    type="checkbox"
                    x-bind:value="record[available.meta.primary_column]"
                    x-model.number="applied.massActions.indices"
                  >
                </td>
              </template>
              <template x-for="column in available.columns">
                <template x-if="column.visibility">
                  <td
                    class="px-2 py-1 max-sm:flex max-sm:justify-between max-sm:before:text-sm max-sm:before:font-semibold max-sm:before:text-neutral-600 max-sm:before:content-[attr(data-label)] sm:px-6 sm:py-4"
                    x-bind:data-label="column.label"
                  >
                    <span x-html="record[column.index]"></span>
                  </td>
                </template>
              </template>
              <template x-if="available.actions.length > 0">
                <td
                  class="px-2 py-1 text-right max-sm:flex max-sm:justify-between max-sm:before:text-sm max-sm:before:font-semibold max-sm:before:text-neutral-600 max-sm:before:content-[attr(data-label)] sm:px-6 sm:py-4"
                  data-label="@lang('shop::app.components.datagrid.table.actions')"
                >
                  <div class="flex justify-end gap-2">
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
  </div>

  <!-- Mobile View -->
  @isset($mobile)
    <div class="divide-y divide-neutral-200 lg:hidden">
      {{ $mobile }}
    </div>
  @endisset

  <!-- Pagination -->
  <template x-if="available.records.length > 0">
    <div class="flex items-center justify-between border-t border-neutral-100 p-4">
      <p class="text-xs font-medium" x-text="paginationText"></p>
      <nav aria-label="@lang('shop::app.components.datagrid.table.page-navigation')" role="navigation">
        <div class="inline-flex items-center rounded-lg border border-neutral-100">
          <button
            class="p-1.5 text-neutral-700 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:text-neutral-200 disabled:hover:bg-transparent lg:p-2"
            x-bind:disabled="available.meta.current_page <= 1"
            x-on:click="changeToPreviousPage"
          >
            <x-lucide-chevron-left class="h-4 w-4" />
          </button>

          <template x-for="page in available.meta.last_page">
            <button
              class="data-[active]:text-primary border-l border-neutral-100 px-2 py-1.5 text-sm text-neutral-700 hover:bg-neutral-50 lg:px-3"
              x-bind:data-active="available.meta.current_page === page"
              x-on:click="changePage(page)"
            >
              <span x-text="page"></span>
            </button>
          </template>

          <button
            class="border-l border-neutral-100 p-1.5 text-neutral-700 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:text-neutral-200 disabled:hover:bg-transparent lg:p-2"
            x-bind:disabled="available.meta.current_page >= available.meta.last_page"
            x-on:click="changeToNextPage()"
          >
            <x-lucide-chevron-right class="h-4 w-4" />
          </button>
        </div>
      </nav>
    </div>
  </template>
</div>

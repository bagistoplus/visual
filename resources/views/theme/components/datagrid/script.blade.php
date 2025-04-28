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
                  this.$toaster.success(data.message);
                })
                .catch(error => {
                  if (error.message) {
                    this.$toaster.error(error.message);
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
                  this.$toaster.success(data.message);
                })
                .catch(error => {
                  if (error.message) {
                    this.$toaster.error(error.message);
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
            this.$toaster.warning("@lang('shop::app.components.datagrid.toolbar.mass-actions.no-records-selected')");
            return false;
          }

          if (!this.applied.massActions.meta.action) {
            this.$toaster.warning("@lang('shop::app.components.datagrid.toolbar.mass-actions.must-select-a-mass-action')");
            return false;
          }

          if (
            this.applied.massActions.meta.action?.options?.length &&
            this.applied.massActions.value === null
          ) {
            this.$toaster.warning("@lang('shop::app.components.datagrid.toolbar.mass-actions.must-select-a-mass-action-option')");
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

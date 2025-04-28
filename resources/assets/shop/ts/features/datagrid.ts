import { defineComponent, defineScope } from '../utils/define-component';

interface Column {
  index: string;
  label: string;
  visibility: boolean;
  sortable: boolean;
  filterable: boolean;
  type: 'string' | 'number' | 'boolean' | 'date' | 'datetime';
  filterable_type?: 'dropdown' | 'text' | 'date_range' | 'datetime_range';
  filterable_options?: { label: string; value: any; name?: string }[]; // name for date ranges
  allow_multiple_values?: boolean;
}

interface Action {
  title: string;
  url: string;
  method: string;
  icon?: string;
}

interface MassAction extends Action {
  options?: Array<{ label: string; value: any }>;
}

interface DataGridMeta {
  current_page: number;
  from: number;
  to: number;
  last_page: number;
  total: number;
  per_page: number;
  per_page_options: number[];
  primary_column: string;
}

interface AppliedFilter {
  index: string;
  label?: string;
  type?: string;
  value: any | any[];
  allow_multiple_values?: boolean;
}

interface ItemRecord {
  [key: string]: any; // Data for each column index
  actions?: Action[]; // Row-specific actions
}

interface AvailableState {
  id: string | null;
  columns: Column[];
  actions: Action[];
  massActions: MassAction[];
  records: ItemRecord[];
  meta: Partial<DataGridMeta>;
}

interface AppliedState {
  massActions: {
    meta: {
      mode: 'none' | 'partial' | 'all';
      action: MassAction | null;
    };
    indices: number[]; // selected rows ids
    value: any | null; // Value for mass action option
  };
  pagination: {
    page: number;
    perPage: number;
  };
  sort: {
    column: string | null;
    order: 'asc' | 'desc' | null;
  };
  filters: {
    columns: AppliedFilter[];
  };
}

interface Messages {
  pagination: {
    showing: string;
    to: string;
    of: string;
  };
  results: string;
}

interface DataGridState {
  src: string;
  isLoading: boolean;
  isFilterDirty: boolean;

  messages: Messages;
  available: AvailableState;
  applied: AppliedState;

  // Computed properties
  haveRecords: boolean;
  haveSelection: boolean;
  paginationText: string;

  // Methods
  loadSavedState(): void;
  applyUrlParams(): void;
  setupWatchers(): void;

  findAppliedColumn(index: string): AppliedFilter | undefined;
  hasAnyAppliedColumnValues(index: string): boolean;
  hasAnyValue(column: AppliedFilter): boolean;
  getAppliedColumnValues(columnIndex: string): any[];
  removeAppliedColumnValue(columnIndex: string, value: any): void;
  removeAppliedColumnAllValues(columnIndex: string): void;
  getColumnValueLabel(column: Column, value: any): any;

  updateGlobalSearch(value: string): void;
  addFilter(value: any, column?: Column | null, additional?: any): void;
  applyColumnValues(column: Column, requestedValue: any, additional?: any): void;
  handleDateFilter(column: Column, appliedColumn: AppliedFilter | undefined, value: any, range?: any): void;
  updateOrCreateColumn(column: Column, appliedColumn: AppliedFilter | undefined, value: any): void;
  shouldSkipValue(appliedColumn: AppliedFilter | undefined, requestedValue: any): boolean;
  applyFilters(): void;

  changeSort(column: Column): void;

  changePagination(perPage: number): void;
  changePage(page: number): void;

  fetchData(): void;
  buildRequestParams(): any;

  toggleSelectAll(): void;
  setCurrentSelectionMode(): void;
  handleAction(action: Action): void;
  handleMassAction(action: MassAction, option?: any): void;
  validateMassAction(): boolean;

  saveToStorage(key: string, value: any): void;
  getFromStorage(key: string): any;
  updateDatagrids(): void;
  notifyExportComponent(): void;
}
interface TableHeaderScope {
  column: Column;
  isSorted: boolean;
  sortOrder: 'asc' | 'desc' | null;
  sort(): void;
}

interface FilterScope {
  column: Column;
  appliedValues: any[];
  hasValues: boolean;
  addValue(value: any, options?: any): void;
  addQuickOptionValue(option: NonNullable<Column['filterable_options']>[number]): void;
  addDateRangeValue(value: any, rangeName: string): void;
  removeValue(value: any): void;
  removeAllValues(): void;
  getValueLabel(value: any): any;
}

interface TableRowScope {
  record: Record<string, any>;
  primaryKey: string;
  isSelected: boolean;
  toggleSelection(): void;
}

interface MassActionScope {
  action: MassAction;
  hasOptions: boolean;
  performAction(option?: any): void;
}

interface PaginationControlScope {
  currentPage: number;
  totalPages: number;
  isFirstPage: boolean;
  isLastPage: boolean;
  goToPage(page: number): void;
  goToPreviousPage(): void;
  goToNextPage(): void;
}

// Added missing scoped interfaces
interface SearchInputScope {
  value: string;
  updateSearch(value: string): void;
}

interface PageSizeSelectorScope {
  value: number;
  options: number[];
  change(value: string): void;
}

interface FilterValueScope {
  value: any;
  label: any;
  remove(): void;
}

type DataGridAPI = DataGridState & {
  $filter: FilterScope;
  $filterValue: FilterValueScope;

  $searchInput: SearchInputScope;
  $massAction: MassActionScope;
  $pagination: PaginationControlScope;
  $pageSizeSelector: PageSizeSelectorScope;

  $tableHeader: TableHeaderScope;
  $tableRow: TableRowScope;
};

export default defineComponent<DataGridAPI>({
  name: 'datagrid',

  setup(props) {
    return {
      src: props.src || '',
      isLoading: false,
      isFilterDirty: false,

      messages: {
        pagination: { showing: 'Showing :firstItem', to: 'to :lastItem', of: 'of :total' },
        results: ':total results',
        ...props.messages,
      },

      available: {
        id: null,
        columns: [],
        actions: [],
        massActions: [],
        records: [],
        meta: {} as DataGridMeta,
      },

      applied: {
        massActions: {
          meta: {
            mode: 'none' as 'none' | 'all' | 'partial',
            action: null,
          },
          indices: [],
          value: null,
        },
        pagination: {
          page: 1,
          perPage: 10,
        },
        sort: {
          column: null,
          order: null,
        },
        filters: {
          columns: [
            {
              index: 'all',
              value: [],
            },
          ],
        },
      },

      // Computed properties

      get haveRecords() {
        return this.available.records.length > 0;
      },

      get haveMassActions() {
        return this.available.massActions.length > 0;
      },

      get haveSelection() {
        return this.applied.massActions.indices.length > 0;
      },

      get paginationText() {
        return [
          this.messages.pagination.showing.replace(':firstItem', this.available.meta.from?.toString()),
          this.messages.pagination.to.replace(':lastItem', this.available.meta.to?.toString()),
          this.messages.pagination.of.replace(':total', this.available.meta.total?.toString()),
        ].join(' ');
      },

      init() {
        this.loadSavedState();
        this.applyUrlParams();
        this.setupWatchers();

        this.fetchData();
      },

      loadSavedState() {
        const datagrids = this.getFromStorage('datagrids') || [];
        const url = this.src.split('?')[0];
        const savedGrid = datagrids.find((grid: any) => grid.src === url);

        if (savedGrid?.applied) {
          if (savedGrid.applied.sort) {
            this.applied.sort = { ...this.applied.sort, ...savedGrid.applied.sort };
          }

          if (savedGrid.applied.pagination) {
            this.applied.pagination = { ...this.applied.pagination, ...savedGrid.applied.pagination };
          }

          if (savedGrid.applied.filters) {
            this.applied.filters = { ...this.applied.filters, ...savedGrid.applied.filters };
          }
        }
      },

      applyUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('search')) {
          const searchValue = urlParams.get('search')!;
          const searchColumn = this.findAppliedColumn('all');

          if (searchColumn) {
            searchColumn.value = [searchValue];
          } else {
            this.applied.filters.columns.push({ index: 'all', value: [searchValue] });
          }
        }
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

      // Filter utilities
      findAppliedColumn(index) {
        return this.applied.filters.columns.find((column) => column.index === index);
      },

      hasAnyAppliedColumnValues(index) {
        const appliedColumn = this.findAppliedColumn(index);
        return appliedColumn ? this.hasAnyValue(appliedColumn) : false;
      },

      hasAnyValue(column) {
        return column.allow_multiple_values ? column.value.length > 0 : !!column.value;
      },

      getAppliedColumnValues(columnIndex) {
        const appliedColumn = this.findAppliedColumn(columnIndex);

        if (!appliedColumn || !this.hasAnyValue(appliedColumn)) {
          return [];
        }

        return Array.isArray(appliedColumn.value) ? appliedColumn.value : [appliedColumn.value];
      },

      removeAppliedColumnValue(columnIndex, value) {
        const appliedColumn = this.findAppliedColumn(columnIndex);

        if (!appliedColumn) {
          return;
        }

        if (['date', 'datetime'].includes(appliedColumn.type as string)) {
          appliedColumn.value = [];
        } else if (appliedColumn.allow_multiple_values) {
          if (Array.isArray(appliedColumn.value)) {
            appliedColumn.value = appliedColumn.value.filter((v: any) => v !== value);
          } else {
            appliedColumn.value = [];
          }
        } else {
          appliedColumn.value = '';
        }

        if (!appliedColumn.value?.length) {
          this.applied.filters.columns = this.applied.filters.columns.filter((column) => column.index !== columnIndex);
        }

        this.isFilterDirty = true;
      },

      removeAppliedColumnAllValues(columnIndex) {
        this.applied.filters.columns = this.applied.filters.columns.filter((column) => column.index !== columnIndex);
        this.isFilterDirty = true;
      },

      getColumnValueLabel(column, value) {
        if (column.filterable_options!.length > 0) {
          const option = column.filterable_options!.find((option) => {
            if (
              ['date_range', 'datetime_range'].includes(column.filterable_type!) ||
              (['date', 'datetime'].includes(column.type) && typeof value === 'string')
            ) {
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

      // Filter & search handlers
      updateGlobalSearch(value) {
        let searchColumn = this.findAppliedColumn('all');

        if (searchColumn) {
          searchColumn.value = value ? [value] : [];
        } else if (value) {
          this.applied.filters.columns.unshift({
            index: 'all',
            value: [value],
          });
        }

        this.applied.pagination.page = 1;
        this.fetchData();
      },

      addFilter(value, column = null, additional = {}) {
        if (additional.quickFilter && ['date', 'datetime'].includes(column?.type as string)) {
          this.applyColumnValues(column!, value);
          return;
        }

        this.applyColumnValues(column!, value, additional);
      },

      applyColumnValues(column, requestedValue, additional = {}) {
        if (!column) {
          return;
        }

        const appliedColumn = this.findAppliedColumn(column.index);
        if (this.shouldSkipValue(appliedColumn, requestedValue)) {
          return;
        }

        if (['date', 'datetime'].includes(column.type as string)) {
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
        if (appliedColumn && Array.isArray(appliedColumn.value)) {
          rangeValues = [...appliedColumn.value[0]];
        }

        if (range.name === 'from') {
          rangeValues[0] = value;
        }

        if (range.name === 'to') {
          rangeValues[1] = value;
        }

        this.updateOrCreateColumn(column, appliedColumn, [rangeValues]);
      },

      updateOrCreateColumn(column, appliedColumn, value) {
        const isDateType = ['date', 'datetime'].includes(column.type as string);
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
        if (!requestedValue) {
          return true;
        }

        if (appliedColumn?.allow_multiple_values) {
          return appliedColumn.value.includes(requestedValue);
        } else {
          return appliedColumn?.value === requestedValue;
        }
      },

      applyFilters() {
        this.applied.pagination.page = 1;
        this.isFilterDirty = false;
        this.fetchData();
      },

      // Sorting
      changeSort(column) {
        if (!column.sortable) return;

        this.applied.sort = {
          column: column.index,
          order: this.applied.sort.order === 'asc' ? 'desc' : 'asc',
        };

        this.applied.pagination.page = 1;
        this.fetchData();
      },

      // Pagination handler
      changePagination(perPage) {
        this.applied.pagination.perPage = perPage;

        this.applied.pagination.page = 1;

        this.fetchData();
      },

      changePage(page) {
        if (page === this.applied.pagination.page) {
          return;
        }

        this.applied.pagination.page = page;
        this.fetchData();
      },

      // Data fetching
      fetchData() {
        this.isLoading = true;
        this.$request(this.src, 'GET', this.buildRequestParams())
          .then((data) => {
            Object.assign(this.available, {
              id: data.id,
              columns: data.columns,
              actions: data.actions,
              massActions: data.mass_actions,
              records: data.records,
              meta: data.meta,
            });
          })
          .finally(() => {
            this.isLoading = false;
          });
      },

      buildRequestParams() {
        const params: {
          pagination: {
            page: number;
            per_page: number;
          };
          sort: {
            column?: string | null;
            order?: 'asc' | 'desc' | null;
          };
          filters: Record<string, any>;
        } = {
          pagination: {
            page: this.applied.pagination.page,
            per_page: this.applied.pagination.perPage,
          },
          sort: {},
          filters: {},
        };

        if (this.applied.sort.column && this.applied.sort.order) {
          params.sort = this.applied.sort;
        }

        this.applied.filters.columns.forEach((column) => {
          params.filters[column.index] = column.value;
        });

        return params;
      },

      // Mass actions (bulk operations)
      toggleSelectAll() {
        const primaryColumn = this.available.meta.primary_column!;
        const currentMode = this.applied.massActions.meta.mode;
        const currentIds = this.available.records.map((record) => record[primaryColumn]);

        if (currentMode === 'none') {
          this.applied.massActions.indices = [...this.applied.massActions.indices, ...currentIds];
          this.applied.massActions.meta.mode = 'all';
        } else {
          this.applied.massActions.indices = this.applied.massActions.indices.filter((id) => !currentIds.includes(id));
          this.applied.massActions.meta.mode = 'none';
        }
      },

      setCurrentSelectionMode() {
        this.applied.massActions.meta.mode = 'none';

        if (this.available.records.length === 0) return;

        const primaryColumn = this.available.meta.primary_column!;
        const selectedCount = this.available.records.filter((record) =>
          this.applied.massActions.indices.includes(record[primaryColumn])
        ).length;

        if (selectedCount > 0) {
          this.applied.massActions.meta.mode = selectedCount === this.available.records.length ? 'all' : 'partial';
        }
      },

      handleAction(action) {
        const method = action.method.toLowerCase();

        if (['post', 'put', 'patch', 'delete'].includes(method)) {
          this.$triggerConfirm({
            onConfirm: () => {
              this.$request(action.url, method as any)
                .then((data) => {
                  this.$toaster.success(data.message);
                  this.fetchData();
                })
                .catch((error) => {
                  if (error.message) {
                    this.$toaster.error(error.message);
                  }
                });
            },
          });
        } else if (method === 'get') {
          window.location.href = action.url;
        } else {
          console.error('Method not supported.');
        }
      },

      handleMassAction(action, option = null) {
        this.applied.massActions.meta.action = action;
        this.applied.massActions.value = option ? option.value : null;

        if (!this.validateMassAction()) {
          return;
        }

        const method = action.method.toLowerCase();

        this.$triggerConfirm({
          onConfirm: () => {
            if (['post', 'put', 'patch', 'delete'].includes(method)) {
              const params: { indices: number[]; value?: any } = {
                indices: this.applied.massActions.indices,
              };

              if (method !== 'delete') {
                params.value = this.applied.massActions.value;
              }

              this.$request(action.url, method as any, params)
                .then((data) => {
                  this.$toaster.success(data.message);
                  this.fetchData();
                })
                .catch((error) => {
                  if (error.message) {
                    this.$toaster.error(error.message);
                  }
                });
            } else {
              console.error('Method not supported.');
            }
          },
        });
      },

      validateMassAction() {
        if (!this.applied.massActions.indices.length) {
          this.$toaster.warning('No records selected');
          return false;
        }

        if (!this.applied.massActions.meta.action) {
          this.$toaster.warning('Must select a mass action');
          return false;
        }

        if (this.applied.massActions.meta.action?.options?.length && this.applied.massActions.value === null) {
          this.$toaster.warning('Must select a mass action option');
          return false;
        }

        return true;
      },

      // Storage & notifications
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
          applied: this.applied,
        };

        const existingIndex = datagrids.findIndex((grid: { src: string }) => grid.src === src);

        if (existingIndex >= 0) {
          gridData.requestCount = datagrids[existingIndex].requestCount + 1;
          datagrids[existingIndex] = gridData;
        } else {
          datagrids.push(gridData);
        }

        this.saveToStorage('datagrids', datagrids);
      },

      notifyExportComponent() {
        document.dispatchEvent(
          new CustomEvent('change-datagrid', {
            detail: {
              available: this.available,
              applied: this.applied,
            },
          })
        );
      },
    };
  },

  parts: {
    totalResults: (api) => ({
      'x-text': () => api.messages.results.replace(':total', (api.available.meta.total ?? 0).toString()),
    }),

    searchInput: defineScope<DataGridAPI, 'searchInput', SearchInputScope>({
      name: 'searchInput',
      setup(api) {
        return {
          get value() {
            const searchColumn = api.findAppliedColumn('all');
            return searchColumn?.value ?? [];
          },

          updateSearch(value) {
            api.updateGlobalSearch(value.trim());
          },
        };
      },
      bindings(_, scope) {
        return {
          'x-bind:value': () => scope.value,
          'x-on:input.debounce.500ms': ($event: Event) =>
            scope.updateSearch(($event.target as HTMLInputElement)?.value || ''),
          'x-on:keyup.enter.prevent': ($event: Event) =>
            scope.updateSearch(($event.target as HTMLInputElement)?.value || ''),
        };
      },
    }),

    // Select All Checkbox
    selectAll: (api) => ({
      'x-bind:checked': () => ['all'].includes(api.applied.massActions.meta.mode),
      '@change': () => api.toggleSelectAll(),
    }),

    // Pagination Controls
    pagination: defineScope<DataGridAPI, 'pagination', PaginationControlScope>({
      name: 'pagination',
      setup(api) {
        return {
          get currentPage() {
            return api.applied.pagination.page;
          },

          get totalPages() {
            return api.available.meta.last_page ?? 0;
          },

          get isFirstPage() {
            return this.currentPage <= 1;
          },

          get isLastPage() {
            return this.currentPage >= this.totalPages;
          },

          goToPage(page) {
            api.changePage(page);
          },

          goToPreviousPage() {
            api.changePage(this.currentPage - 1);
          },

          goToNextPage() {
            api.changePage(this.currentPage + 1);
          },
        };
      },
    }),

    // Pagination Per Page Select
    pageSizeSelector: defineScope<DataGridAPI, 'pageSizeSelector', PageSizeSelectorScope>({
      name: 'pageSizeSelector',
      setup(api) {
        return {
          get value() {
            return api.applied.pagination.perPage;
          },

          get options() {
            return api.available.meta.per_page_options || [10, 25, 50, 100];
          },

          change(value) {
            api.changePagination(parseInt(value, 10));
          },
        };
      },
      bindings(api, scope) {
        return {
          'x-bind:value': () => scope.value,
          'x-on:change': ($event: Event) => scope.change(($event.target as HTMLSelectElement).value),
        };
      },
    }),

    // Mass Action
    massAction: defineScope<DataGridAPI, 'massAction', MassActionScope>({
      name: 'massAction',
      setup(api, el, { value }) {
        const actionIndex = parseInt(value);
        const action = api.available.massActions[actionIndex];

        return {
          action,

          performAction(option = null) {
            api.handleMassAction(action, option);
          },

          get hasOptions() {
            return action.options?.length! > 0;
          },
        };
      },
    }),

    // Mass Action Option
    massActionOption(api, el, { value }) {
      const optionIndex = parseInt(value);
      const option = api.$massAction.action.options![optionIndex];

      return {
        'x-on:click': () => api.$massAction.performAction(option),
      };
    },

    // Table Header
    tableHeader: defineScope<DataGridAPI, 'tableHeader', TableHeaderScope>({
      name: 'tableHeader',
      setup(api, el, { value }) {
        const column = api.available.columns.find((col) => col.index === value) as Column;

        return {
          column,

          get isSorted() {
            return api.applied.sort.column === column.index;
          },

          get sortOrder() {
            return this.isSorted ? api.applied.sort.order : null;
          },

          sort() {
            api.changeSort(column);
          },
        };
      },

      bindings(api, scope) {
        return {
          'x-bind:class': () => (scope.column.sortable ? 'cursor-pointer' : ''),
          'x-on:click': () => scope.column.sortable && scope.sort(),
        };
      },
    }),

    // Table Row
    tableRow: defineScope<DataGridAPI, 'tableRow', TableRowScope>({
      name: 'tableRow',
      setup(api, el, { value: record }) {
        const primaryKey = api.available.meta.primary_column as string;

        return {
          record,
          primaryKey,

          get isSelected() {
            return api.applied.massActions.indices.includes(record[primaryKey]);
          },

          toggleSelection() {
            const currentIndices = api.applied.massActions.indices;
            const id = record[primaryKey];

            if (currentIndices.includes(id)) {
              api.applied.massActions.indices = currentIndices.filter((i) => i !== id);
            } else {
              api.applied.massActions.indices = [...currentIndices, id];
            }
          },
        };
      },

      bindings(_, scope) {
        return {
          'x-bind:data-selected': () => (scope.isSelected ? 'true' : 'false'),
        };
      },
    }),

    // Row Checkbox
    tableRowCheckbox: (api) => ({
      'x-bind:checked': () => api.$tableRow.isSelected,
      'x-bind:value': () => api.$tableRow.record[api.$tableRow.primaryKey],
      'x-on:change': () => api.$tableRow.toggleSelection(),
    }),

    // Filter
    filter: defineScope<DataGridAPI, 'filter', FilterScope>({
      name: 'filter',
      setup(api, el, { value }) {
        const columnIndex = value;
        const column = api.available.columns.find((col) => col.index === columnIndex) as Column;

        return {
          column,

          get appliedValues() {
            return api.getAppliedColumnValues(columnIndex);
          },

          get hasValues() {
            return api.hasAnyAppliedColumnValues(columnIndex);
          },

          addValue(value, options = {}) {
            api.addFilter(value, column, options);
          },

          addQuickOptionValue(option) {
            this.addValue(option.name, { quickFilter: option });
          },

          addDateRangeValue(value, rangeName) {
            this.addValue(value, { range: { name: rangeName } });
          },

          removeValue(value) {
            api.removeAppliedColumnValue(columnIndex, value);
          },

          removeAllValues() {
            api.removeAppliedColumnAllValues(columnIndex);
          },

          getValueLabel(value) {
            return api.getColumnValueLabel(column, value);
          },
        };
      },
    }),

    clearFilter: (api) => ({
      'x-show': () => api.$filter.hasValues,
      'x-on:click': () => api.$filter.removeAllValues(),
    }),

    // Filter Tag
    filterValue: defineScope<DataGridAPI, 'filterValue', FilterValueScope>({
      name: 'filterValue',
      setup(api, el, { value }) {
        const tagValue = value;

        return {
          value: tagValue,

          get label() {
            return api.$filter.getValueLabel(tagValue);
          },

          remove() {
            api.$filter.removeValue(tagValue);
          },
        };
      },
    }),

    // Filter Apply Button
    applyFilters: (api) => ({
      'x-bind:disabled': () => !api.isFilterDirty,
      'x-on:click': () => api.applyFilters(),
    }),
  },
});

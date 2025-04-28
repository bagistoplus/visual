@props(['src' => ''])

@php
  $alpineProps = [
      'src' => $src,
      'messages' => [
          'pagination' => [
              'showing' => trans('shop::app.components.datagrid.table.showing'),
              'to' => trans('shop::app.components.datagrid.table.to'),
              'of' => trans('shop::app.components.datagrid.table.of'),
          ],
          'results' => trans('shop::app.components.datagrid.toolbar.results'),
      ],
  ];
@endphp

<div x-data x-datagrid="@js($alpineProps)">
  <!-- Toolbar -->
  <div class="border-b border-neutral-200 p-3">
    <div class="flex flex-col gap-4 md:flex-row">
      <div class="flex flex-1 items-center justify-between gap-4">
        <!-- Mass Actions Dropdown (if records are selected) -->
        <template x-if="haveSelection">
          <x-shop::datagrid.mass-actions />
        </template>

        <!-- Global Search Input (shown if no mass actions are active) -->
        <template x-if="!haveSelection">
          <x-shop::datagrid.search />
        </template>

        <p class="hidden md:block" x-datagrid:total-results></p>

        <!-- Filter Drawer -->
        <x-shop::datagrid.filters class="ml-auto" />
      </div>

      <!-- Pagination Controls (Toolbar Bottom) -->
      <div class="flex items-center justify-between gap-4">
        <x-shop::ui.form.select size="sm" x-datagrid:page-size-selector="">
          <template x-for="option in $pageSizeSelector.options">
            <option x-bind:value="option" x-text="option"></option>
          </template>
        </x-shop::ui.form.select>

        <div class="md:hidden" x-datagrid:total-results></div>
      </div>
    </div>
  </div>

  <!-- Table (hidden on mobile if mobile slot provided) -->
  <div class="@isset($mobile)hidden lg:block @endisset overflow-x-auto max-sm:p-2">
    <x-shop::datagrid.table />
  </div>

  <!-- Mobile View -->
  @isset($mobile)
    <div class="divide-y divide-neutral-200 lg:hidden">
      {{ $mobile }}
    </div>
  @endisset

  <!-- Pagination -->
  <template x-if="haveRecords">
    <x-shop::datagrid.pagination />
  </template>
</div>

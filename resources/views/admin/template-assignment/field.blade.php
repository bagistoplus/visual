@if (($enabled ?? false) && isset($type, $model))
  @if ($accordion)
    <x-admin::accordion>
      <x-slot:header>
        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
          @lang('visual::admin.template-assignment.label')
        </p>
      </x-slot>

      <x-slot:content>
        @include('visual::admin.template-assignment.control')
      </x-slot>
    </x-admin::accordion>
  @else
    <div class="box-shadow relative rounded bg-white p-4 dark:bg-gray-900">
      @include('visual::admin.template-assignment.control')
    </div>
  @endif
@endif

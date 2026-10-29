<x-admin::form :action="$action" :method="$method">
  <x-admin::modal :ref="$ref">
    <x-slot:header>
      <p class="text-lg font-bold text-gray-800 dark:text-white">
        {{ $title }}
      </p>
    </x-slot>

    <x-slot:content>
      <x-admin::form.control-group>
        <x-admin::form.control-group.label class="required">
          @lang('visual::admin.themes.copy-name')
        </x-admin::form.control-group.label>

        <x-admin::form.control-group.control
          type="text"
          name="name"
          rules="required|max:100"
          :value="$name"
          :label="trans('visual::admin.themes.copy-name')"
        />

        <x-admin::form.control-group.error control-name="name" />
      </x-admin::form.control-group>

      <x-admin::form.control-group class="!mb-0">
        <x-admin::form.control-group.label>
          @lang('visual::admin.themes.copy-description')
        </x-admin::form.control-group.label>

        <x-admin::form.control-group.control
          type="textarea"
          name="description"
          rules="max:500"
          :value="$description"
          :label="trans('visual::admin.themes.copy-description')"
        />

        <x-admin::form.control-group.error control-name="description" />
      </x-admin::form.control-group>
    </x-slot>

    <x-slot:footer>
      <button type="submit" class="primary-button">
        {{ $submitLabel }}
      </button>
    </x-slot>
  </x-admin::modal>
</x-admin::form>

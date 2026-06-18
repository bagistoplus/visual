<x-admin::form.control-group>
    <x-admin::form.control-group.label>
        @lang('visual::admin.template-assignment.label')
    </x-admin::form.control-group.label>

    <x-admin::form.control-group.control
        type="select"
        name="visual_template"
        :value="$selected"
        :label="trans('visual::admin.template-assignment.label')"
    >
        <option value="">
            {{ $defaultLabel }}
        </option>

        @foreach ($templates as $template)
            <option
                value="{{ $template->key }}"
                @selected($selected === $template->key)
            >
                {{ $template->label }}
            </option>
        @endforeach
    </x-admin::form.control-group.control>

    <x-admin::form.control-group.error control-name="visual_template" />
</x-admin::form.control-group>

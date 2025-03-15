<div class="rounded-lg bg-white shadow-sm">
  <div class="border-b border-neutral-100 p-4">
    <div class="flex items-center justify-between">
      <h1 class="font-serif text-2xl text-neutral-700">
        @lang('shop::app.customers.account.profile.edit.edit-profile')
      </h1>
      <x-shop::ui.button
        color="primary"
        variant="soft"
        icon="lucide-arrow-left"
        size="sm"
        href="{{ route('shop.customers.account.profile.index') }}"
      >
        Back
      </x-shop::ui.button>
    </div>
  </div>

  <form
    class="p-6"
    action="{{ route('shop.customers.account.profile.update') }}"
    enctype="multipart/form-data"
    method="POST"
    x-data="{
        avatar: '{{ $customer->image_url }}',
        handleImageUpload(event) {
            const file = event.target.files?.[0];
    
            if (file) {
                const reader = new FileReader();
                reader.onloadend = () => {
                    this.avatar = reader.result;
                };
                reader.readAsDataURL(file);
            }
        }
    }"
  >
    @csrf

    <div class="mb-8 text-center">
      <div class="relative inline-block">
        <div class="h-32 w-32 overflow-hidden rounded-full bg-neutral-100">
          <template x-if="avatar">
            <img
              x-bind:src="avatar"
              alt="Profile"
              class="h-full w-full object-cover"
            />
          </template>
          <div x-show="!avatar" class="flex h-full w-full items-center justify-center">
            <x-lucide-user class="h-16 w-16 text-gray-400" />
          </div>
        </div>

        <label class="bg-primary absolute bottom-0 right-0 cursor-pointer rounded-full p-2 text-white hover:opacity-90">
          <x-lucide-camera class="h-5 w-5" />
          <input
            type="file"
            name="image[]"
            class="hidden"
            accept="image/*"
            x-on:change="handleImageUpload"
          >
        </label>
      </div>
    </div>

    <div class="space-y-8">
      <div>
        <h2 class="mb-4 text-lg font-medium text-neutral-700">
          Personal Information
        </h2>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <x-shop::ui.form.input
            required
            name="first_name"
            :label="trans('shop::app.customers.account.profile.edit.first-name')"
            :value="old('first_name') ?? $customer->first_name"
          />

          <x-shop::ui.form.input
            required
            name="last_name"
            :label="trans('shop::app.customers.account.profile.edit.last-name')"
            :value="old('last_name') ?? $customer->last_name"
          />

          <x-shop::ui.form.input
            required
            name="email"
            type="email"
            prepend-icon="lucide-mail"
            :label="trans('shop::app.customers.account.profile.edit.email')"
            :value="old('email') ?? $customer->email"
          />

          <x-shop::ui.form.input
            required
            name="phone"
            prepend-icon="lucide-phone"
            :label="trans('shop::app.customers.account.profile.edit.phone')"
            :value="old('phone') ?? $customer->phone"
          />

          <x-shop::ui.form.select
            required
            name="gender"
            prepend-icon="lucide-users"
            :label="trans('shop::app.customers.account.profile.edit.gender')"
            :value="old('gender') ?? $customer->gender"
          >
            <option disabled value="">Select gender</option>

            <option value="Male">
              @lang('shop::app.customers.account.profile.edit.male')
            </option>

            <option value="Female">
              @lang('shop::app.customers.account.profile.edit.female')
            </option>

            <option value="Other">
              @lang('shop::app.customers.account.profile.edit.other')
            </option>
          </x-shop::ui.form.select>

          <x-shop::ui.form.input
            type="date"
            name="date_of_birth"
            prepend-icon="lucide-calendar"
            :label="trans('shop::app.customers.account.profile.edit.dob')"
            :value="old('date_of_birth') ?? $customer->date_of_birth"
          />
        </div>
      </div>

      <div>
        <h2 class="mb-4 text-lg font-medium text-neutral-700">
          Update Password
        </h2>

        <div class="max-w-md space-y-4">
          <x-shop::ui.form.input
            type="password"
            name="current_password"
            prepend-icon="lucide-lock"
            :label="trans('shop::app.customers.account.profile.edit.current-password')"
            :placeholder="trans('shop::app.customers.account.profile.edit.current-password')"
          />

          <x-shop::ui.form.input
            type="password"
            name="new_password"
            prepend-icon="lucide-lock"
            :label="trans('shop::app.customers.account.profile.edit.new-password')"
            :placeholder="trans('shop::app.customers.account.profile.edit.new-password')"
          />

          <x-shop::ui.form.input
            type="password"
            name="new_password_confirmation"
            prepend-icon="lucide-lock"
            :label="trans('shop::app.customers.account.profile.edit.confirm-password')"
            :placeholder="trans('shop::app.customers.account.profile.edit.confirm-password')"
          />

        </div>
      </div>

      <div>
        <h2 class="mb-4 text-lg font-medium text-neutral-700">
          Preference
        </h2>

        <label class="gap flex items-center gap-2">
          <input
            type="checkbox"
            name="subscribed_to_news_letter"
            @checked($customer->subscribed_to_news_letter)
          >
          <span>
            @lang('shop::app.customers.account.profile.edit.subscribe-to-newsletter')
          </span>
        </label>
      </div>

      <x-shop::ui.button class="px-12">
        @lang('shop::app.customers.account.profile.edit.save')
      </x-shop::ui.button>
    </div>
  </form>
</div>

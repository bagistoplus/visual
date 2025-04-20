<div class="flex items-center justify-center px-4 py-12">
  <div class="bg-surface-alt text-secondary w-full max-w-lg space-y-8 rounded-2xl p-8 shadow-sm">
    <div class="text-center">
      <h2 class="text-3xl font-semibold">
        @lang('shop::app.customers.signup-form.page-title')
      </h2>
      <p class="mt-2">
        @lang('shop::app.customers.signup-form.form-signup-text')
      </p>
    </div>

    <form
      class="space-y-6"
      method="post"
      action="{{ route('shop.customers.register.store') }}"
      x-data="{ passwordType: 'password', confirmType: 'password' }"
    >
      @csrf
      <div class="space-y-4">
        <div>
          <label for="first_name" class="block text-sm font-medium">
            @lang('shop::app.customers.signup-form.first-name')
          </label>
          <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <x-lucide-user class="h-5 w-5" />
            </div>
            <input
              id="first_name"
              name="first_name"
              type="text"
              required
              autocomplete="first_name"
              value="{{ old('first_name') }}"
              placeholder="{{ trans('shop::app.customers.signup-form.first-name') }}"
              class="border-surface-alt-600 py-3 pl-10"
            >
          </div>
          @error('first_name')
            <span class="text-danger-500 text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label for="last_name" class="block text-sm font-medium">
            @lang('shop::app.customers.signup-form.last-name')
          </label>
          <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <x-lucide-user class="h-5 w-5" />
            </div>
            <input
              id="last_name"
              name="last_name"
              type="text"
              required
              autocomplete="last_name"
              value="{{ old('last_name') }}"
              placeholder="{{ trans('shop::app.customers.signup-form.last-name') }}"
              class="border-surface-alt-600 py-3 pl-10"
            >
          </div>
          @error('last_name')
            <span class="text-danger-500 text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label for="email" class="block text-sm font-medium">
            @lang('shop::app.customers.signup-form.email')
          </label>
          <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <x-lucide-mail class="h-5 w-5" />
            </div>
            <input
              id="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="border-surface-alt-600 py-3 pl-10"
              placeholder="email@example.com"
              value="{{ old('email') }}"
            >
          </div>
          @error('email')
            <span class="text-danger-500 text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label for="password" class="block text-sm font-medium">
            @lang('shop::app.customers.signup-form.password')
          </label>
          <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <x-lucide-lock class="h-5 w-5" />
            </div>
            <input
              id="password"
              name="password"
              autocomplete="current-password"
              required
              placeholder="{{ trans('shop::app.customers.signup-form.password') }}"
              value="{{ old('password') }}"
              class="border-surface-alt-600 py-3 pl-10 pr-12"
              x-bind:type="passwordType"
            >
            <button
              type="button"
              class="absolute inset-y-0 right-0 flex items-center pr-3"
              x-on:click="passwordType = passwordType === 'password' ? 'text' : 'password'"
            >
              <x-lucide-eye x-show="passwordType === 'password'" class="h-5 w-5" />
              <x-lucide-eye-off x-show="passwordType === 'text'" class="h-5 w-5" />
            </button>
          </div>
          @error('password')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label for="password_confirmation" class="block text-sm font-medium">
            @lang('shop::app.customers.signup-form.confirm-pass')
          </label>
          <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <x-lucide-lock class="h-5 w-5" />
            </div>
            <input
              id="password_confirmation"
              name="password_confirmation"
              autocomplete="current-password"
              required
              placeholder="{{ trans('shop::app.customers.signup-form.confirm-pass') }}"
              value="{{ old('password_confirmation') }}"
              class="border-surface-alt-600 py-3 pl-10 pr-12"
              x-bind:type="confirmType"
            >
            <button
              type="button"
              class="absolute inset-y-0 right-0 flex items-center pr-3"
              x-on:click="confirmType = confirmType === 'password' ? 'text' : 'password'"
            >
              <x-lucide-eye x-show="confirmType === 'password'" class="h-5 w-5" />
              <x-lucide-eye-off x-show="confirmType === 'text'" class="h-5 w-5" />
            </button>
          </div>
          @error('password_confirmation')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>
      </div>

      @if (core()->getConfigData('customer.settings.create_new_account_options.news_letter'))
        <label class="flex items-center text-sm">
          <input
            name="is_subscribed"
            type="checkbox"
            class="border-surface-alt-600 h-4 w-4"
          >
          <span class="ml-2">
            @lang('shop::app.customers.signup-form.subscribe-to-newsletter')
          </span>
        </label>
      @endif

      <!-- Captcha -->
      @if (core()->getConfigData('customer.captcha.credentials.status'))
        <div class="[&_.control-error]:text-danger mt-4 flex flex-col items-center gap-2 [&_.control-error]:text-xs">
          {!! Captcha::render() !!}
        </div>
      @endif

      <button type="submit"
        class="bg-primary focus:ring-primary text-primary-50 w-full rounded-lg border border-transparent px-4 py-3 shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2"
      >
        @lang('shop::app.customers.signup-form.button-title')
      </button>

      <p class="text-center text-sm">
        @lang('shop::app.customers.signup-form.account-exists')
        <a class="text-primary hover:opacity-80" href="{{ route('shop.customer.session.index') }}">
          @lang('shop::app.customers.signup-form.sign-in-button')
        </a>
      </p>
    </form>
  </div>
</div>

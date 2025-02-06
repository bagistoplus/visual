<div class="flex items-center justify-center px-4 py-12">
  <div class="bg-surface-alt text-secondary w-full max-w-md space-y-8 rounded-2xl p-8 shadow-sm">
    <div class="text-center">
      <h2 class="text-3xl font-semibold">
        @lang('shop::app.customers.login-form.page-title')
      </h2>
      <p class="mt-2">
        @lang('shop::app.customers.login-form.form-login-text')
      </p>
    </div>
    <form
      method="POST"
      action="{{ route('shop.customer.session.create') }}"
      class="grid gap-4"
      x-data="{ passwordType: 'password' }"
    >
      @csrf
      <div class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium">
            @lang('shop::app.customers.login-form.email')
          </label>
          <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <x-lucide-mail class="h-5 w-5" />
            </div>
            <input
              id="email"
              type="email"
              name="email"
              autocomplete="email"
              class="border-surface-alt-600 focus:ring-primary focus:border-primary block w-full rounded-lg border py-3 pl-10 pr-3 focus:ring-2"
              placeholder="Enter your email"
            >
          </div>
          @error('email')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label for="password" class="block text-sm font-medium">
            @lang('shop::app.customers.login-form.password')
          </label>
          <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <x-lucide-lock class="h-5 w-5" />
            </div>
            <input
              id="password"
              name="password"
              autocomplete="current-password"
              placeholder="Enter your password"
              class="border-surface-alt-600 focus:ring-primary focus:border-primary block w-full rounded-lg border py-3 pl-10 pr-12 focus:ring-2"
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
          @error('email')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="flex items-center justify-end">
        <div class="text-sm">
          <a class="text-primary hover:opacity-80" href="{{ route('shop.customers.forgot_password.create') }}">
            @lang('shop::app.customers.login-form.forgot-pass')
          </a>
        </div>
      </div>

      <!-- Captcha -->
      @if (core()->getConfigData('customer.captcha.credentials.status'))
        <div class="[&_.control-error]:text-danger mt-4 flex flex-col items-center gap-2 [&_.control-error]:text-xs">
          {!! Captcha::render() !!}
        </div>
      @endif

      <button type="submit"
        class="bg-primary focus:ring-primary text-primary-50 w-full rounded-lg border border-transparent px-4 py-3 shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2"
      >
        <span>
          @lang('shop::app.customers.login-form.button-title')
        </span>
      </button>

      <p class="text-center text-sm">
        @lang('shop::app.customers.login-form.new-customer')
        <a class="text-primary hover:opacity-80"href="/register">
          @lang('shop::app.customers.login-form.create-your-account')
        </a>
      </p>
    </form>
  </div>
</div>

@push('scripts')
  {!! Captcha::renderJS() !!}
@endpush

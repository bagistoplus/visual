<div class="flex items-center justify-center px-4 py-16">
  <div class="bg-surface-alt text-secondary w-full max-w-md space-y-8 rounded-2xl p-8 shadow-sm">
    <div class="text-center">
      <h2 class="text-3xl font-semibold">
        @lang('shop::app.customers.reset-password.title')
      </h2>
    </div>

    <form
      method="POST"
      action="{{ route('shop.customers.reset_password.store') }}"
      class="grid gap-4"
      x-data="{ passwordType: 'password', confirmType: 'password' }"
    >
      @csrf
      <input
        type="hidden"
        name="token"
        value="{{ $token }}"
      >

      <div class="space-y-4">
        <div>
          <label
            for="email"
            class="block text-sm font-medium"
            required
          >
            @lang('shop::app.customers.reset-password.email')
          </label>
          <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <x-lucide-mail class="h-5 w-5" />
            </div>
            <input
              id="email"
              required
              type="email"
              name="email"
              autocomplete="email"
              value="{{ old('email') }}"
              class="border-surface-alt-600 py-3 pl-10 pr-12"
              placeholder="email@example.com"
            >
          </div>
          @error('email')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label
            for="password"
            class="block text-sm font-medium"
            required
          >
            @lang('shop::app.customers.reset-password.password')
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
              placeholder="{{ trans('shop::app.customers.reset-password.password') }}"
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
          <label
            for="password_confirmation"
            class="block text-sm font-medium"
            required
          >
            @lang('shop::app.customers.reset-password.confirm-password')
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
              placeholder="{{ trans('shop::app.customers.reset-password.confirm-password') }}"
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

      <button type="submit"
        class="bg-primary focus:ring-primary text-primary-50 w-full rounded-lg border border-transparent px-4 py-3 shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2"
      >
        <span>
          @lang('shop::app.customers.reset-password.submit-btn-title')
        </span>
      </button>
    </form>
  </div>
</div>

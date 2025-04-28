<div class="flex items-center justify-center px-4 py-16">
  <div class="bg-surface-alt text-secondary w-full max-w-md space-y-8 rounded-2xl p-8 shadow-sm">
    <div class="text-center">
      <h2 class="text-3xl font-semibold">
        @lang('shop::app.customers.forgot-password.title')
      </h2>
      <p class="mt-2">
        @lang('shop::app.customers.forgot-password.forgot-password-text')
      </p>
    </div>

    <form method="POST" action="{{ route('shop.customers.forgot_password.store') }}" class="grid gap-4">
      @csrf
      <div class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium" required>
            @lang('shop::app.customers.login-form.email')
          </label>
          <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <x-lucide-mail class="h-5 w-5" />
            </div>
            <input id="email" required type="email" name="email" autocomplete="email" class="border-surface-alt-600 py-3 pl-10 pr-12" placeholder="email@example.com">
          </div>
          @error('email')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>
      </div>
      <!-- Captcha -->
      @if (core()->getConfigData('customer.captcha.credentials.status'))
        <div class="[&_.control-error]:text-danger mt-4 flex flex-col items-center gap-2 [&_.control-error]:text-xs">
          {!! Captcha::render() !!}
        </div>
      @endif

      <button type="submit"
        class="bg-primary focus:ring-primary text-primary-50 w-full rounded-lg border border-transparent px-4 py-3 shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2">
        <span>
          @lang('shop::app.customers.forgot-password.submit')
        </span>
      </button>

      <p class="text-center text-sm">
        @lang('shop::app.customers.forgot-password.back')
        <a class="text-primary hover:opacity-80" href="{{ route('shop.customer.session.index') }}">
          @lang('shop::app.customers.forgot-password.sign-in-button')
        </a>
      </p>
    </form>
  </div>
</div>

@push('scripts')
  {!! Captcha::renderJS() !!}
@endpush

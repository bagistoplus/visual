@push('scripts')
  {!! Captcha::renderJS() !!}
@endpush

<div class="py-12">
  <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
    <div class="bg-surface-alt rounded-2xl p-8 text-neutral-600 shadow-sm">
      <h1 class="mb-6 font-serif text-3xl text-neutral-800">
        @lang('shop::app.home.contact.title')
      </h1>

      <p class="text-lg">
        @lang('shop::app.home.contact.about')
      </p>

      <form method="POST" action="{{ route('shop.home.contact_us.send_mail') }}" class="space-y-6">
        @csrf

        <div>
          <label for="name" class="block text-sm font-medium" required>
            @lang('shop::app.home.contact.name')
          </label>

          <input id="name" required type="text" name="name" class="border-surface-alt-600 mt-1" placeholder="{{ trans('shop::app.home.contact.name') }}">

          @error('name')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label for="email" class="block text-sm font-medium" required>
            @lang('shop::app.home.contact.email')
          </label>

          <input id="email" required type="text" name="email" autocomplete="email" class="border-surface-alt-600 mt-1"
            placeholder="{{ trans('shop::app.home.contact.email') }}">

          @error('email')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label for="contact" class="block text-sm font-medium">
            @lang('shop::app.home.contact.phone-number')
          </label>

          <input id="contact" type="text" name="contact" class="border-surface-alt-600 mt-1" placeholder="{{ trans('shop::app.home.contact.phone-number') }}">

          @error('contact')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label for="message" class="block text-sm font-medium" required>
            @lang('shop::app.home.contact.desc')
          </label>

          <textarea id="message" name="message" required class="mt-1 min-h-[150px] w-full" placeholder="{{ trans('shop::app.home.contact.describe-here') }}"></textarea>

          @error('message')
            <span class="text-danger text-xs">{{ $message }}</span>
          @enderror
        </div>

        <!-- Captcha -->
        @if (core()->getConfigData('customer.captcha.credentials.status'))
          <div class="[&_.control-error]:text-danger mt-4 flex flex-col items-center gap-2 [&_.control-error]:text-xs">
            {!! Captcha::render() !!}
          </div>
        @endif

        <button type="submit"
          class="bg-primary flex w-full items-center justify-center gap-2 rounded-full px-8 py-3 text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
          <x-lucide-send class="h-5 w-5" />
          @lang('shop::app.home.contact.submit')
        </button>
      </form>
    </div>
  </div>
</div>

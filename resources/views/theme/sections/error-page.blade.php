<div class="flex min-h-screen items-center justify-center p-4">
  <div class="w-full max-w-md text-center">
    <div class="mb-8">
      <div class="bg-danger-50 mb-6 inline-flex h-20 w-20 items-center justify-center rounded-full">
        <x-lucide-file-question class="text-danger h-10 w-10" />
      </div>
      <h1 class="text-secondary mb-4 font-serif text-4xl">
        @lang("admin::app.errors.{$errorCode}.title")
      </h1>
      <p class="text-secondary mb-2">
        {{ $errorCode === 503 && core()->getCurrentChannel()->maintenance_mode_text != ''
            ? core()->getCurrentChannel()->maintenance_mode_text
            : trans("admin::app.errors.{$errorCode}.description") }}
      </p>
    </div>
    <div class="flex flex-col justify-center gap-4 sm:flex-row">
      <button class="border-primary text-primary hover:bg-primary inline-flex items-center justify-center rounded-full border px-6 py-3 transition-colors hover:text-white">
        <x-lucide-arrow-left class="mr-2 h-5 w-5" />
        Go Back
      </button>

      <a class="bg-primary text-primary-50 inline-flex items-center justify-center rounded-full px-6 py-3 transition-opacity hover:opacity-90" href="{{ url('/') }}">
        <x-lucide-home class="mr-2 h-5 w-5" />
        Return Home
      </a>
    </div>
  </div>
</div>

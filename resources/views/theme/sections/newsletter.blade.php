<section class="bg-surface-alt py-16">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <h2 class="mb-4 text-center font-serif text-3xl text-neutral-700">
      {{ $section->settings->title }}
    </h2>
    <p class="mx-auto mb-8 max-w-2xl text-center text-neutral-600">
      {{ $section->settings->description }}
    </p>
    <form
      method="POST"
      action="{{ route('shop.subscription.store') }}"
      class="mx-auto max-w-md"
    >
      @csrf
      <div class="flex gap-4">
        <input
          type="email"
          name="email"
          autocomplete="on"
          placeholder="Enter your email"
          class="rounded-full"
        >
        <button type="submit" class="bg-primary text-primary-100 rounded-full px-6">
          Subscribe
        </button>
      </div>

      @error('email')
        <p class="text-danger-500 text-sm italic">{{ $message }}</p>
      @enderror
      <div>
    </form>
  </div>
</section>

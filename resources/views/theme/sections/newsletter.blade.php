<section class="bg-surface-alt py-16">
  <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
    <h2 class="mb-4 font-serif text-3xl text-neutral-700">
      {{ $section->settings->title }}
    </h2>
    <p class="mx-auto mb-8 max-w-2xl text-neutral-600">
      {{ $section->settings->description }}
    </p>
    <form class="mx-auto max-w-md">
      <div class="flex gap-4">
        <input type="email" placeholder="Enter your email" class="rounded-full" required>
        <button type="submit" class="bg-primary text-primary-100 rounded-full px-6">
          Subscribe
        </button>
      </div>
    </form>
  </div>
</section>

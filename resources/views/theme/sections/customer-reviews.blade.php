<div class="bg-background rounded-lg shadow-sm">
  <div class="border-b border-neutral-100 p-4">
    <div class="flex items-center justify-between">
      <h1 class="font-serif text-2xl text-neutral-700">
        @lang('shop::app.customers.account.reviews.title')
      </h1>
    </div>
  </div>
  <div class="space-y-6 p-6">
    @foreach ($reviews as $review)
      <div class="relative rounded-lg border border-neutral-100 p-4">
        <div class="flex gap-4">

          <!-- Image -->
          <img
            src="{{ $review->product->base_image_url }}"
            alt="Review Image"
            class="h-20 w-20 rounded-md object-cover md:h-32 md:w-32"
          >

          <!-- Content -->
          <div class="flex w-full flex-col">
            <div class="flex flex-col sm:justify-between md:flex-row md:items-center">
              <a class="text-base font-medium text-neutral-700 before:absolute before:inset-0 sm:text-xl"
                href="{{ route('shop.product_or_category.index', $review->product->url_key) }}"
              >
                {{ $review->title }}
              </a>
              <x-shop::star-rating :rating="$review->rating" />
            </div>
            <p class="mt-1.5 text-xs text-zinc-500 sm:text-sm">
              {{ $review->created_at }}
            </p>
            <p class="mt-2 hidden text-xs text-zinc-500 sm:block sm:text-base">
              {{ $review->comment }}
            </p>
          </div>
        </div>

        <p class="mt-2 text-xs sm:hidden">
          {{ $review->comment }}
        </p>
      </div>
    @endforeach
  </div>
</div>

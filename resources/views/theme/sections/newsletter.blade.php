<div class="bg-gray-100">
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
    <div class="relative overflow-hidden rounded-2xl px-6 py-10 sm:px-12 sm:py-20"
      style="background-color: {{ $section->settings->background_color }}; color: {{ $section->settings->text_color }};">
      <div class="relative">
        <div class="sm:text-center">
          <h2 class="tracking-tigh text-3xl font-extrabold sm:text-4xl">
            {{ $section->settings->title }}
          </h2>
          <p class="mx-auto mt-6 max-w-2xl text-lg opacity-75">
            {{ $section->settings->description }}
          </p>
        </div>
        <form class="mt-12 sm:mx-auto sm:flex sm:max-w-lg">
          <div class="min-w-0 flex-1">
            <label for="email" class="sr-only">Email address</label>
            <input id="email" type="email"
              class="block w-full rounded-md border border-transparent px-5 py-3 text-base text-gray-900 placeholder-gray-500 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600"
              placeholder="Enter your email">
          </div>
          <div class="mt-4 sm:ml-3 sm:mt-0">
            <button type="submit"
              class="block w-full rounded-md border border-transparent px-5 py-3 text-base font-medium shadow focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 sm:px-10"
              style="background-color: {{ $section->settings->button_color }}; color: {{ $section->settings->button_text_color }};">
              Subscribe
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

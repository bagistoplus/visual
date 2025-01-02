<footer class="bg-gray-900">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
    <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
      @foreach ([['title' => 'Company', 'links' => ['About', 'Careers', 'Contact']], ['title' => 'Shop', 'links' => ['Women', 'Men', 'Accessories']], ['title' => 'Support', 'links' => ['Shipping', 'Returns', 'FAQ']], ['title' => 'Legal', 'links' => ['Privacy', 'Terms']]] as $section)
        <div>
          <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">
            {{ $section['title'] }}
          </h3>
          <ul class="mt-4 space-y-4">
            @foreach ($section['links'] as $link)
              <li>
                <a href="#" class="text-base text-gray-300 hover:text-white">
                  {{ $link }}
                </a>
              </li>
            @endforeach
          </ul>
        </div>
      @endforeach
    </div>
    <div class="mt-12 border-t border-gray-800 pt-8">
      <p class="text-base text-gray-400 xl:text-center">
        © 2024 Store, Inc. All rights reserved.
      </p>
    </div>
  </div>
</footer>

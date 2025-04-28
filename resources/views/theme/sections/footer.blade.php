@php
  $socials = [
      'facebook_url' => 'lucide-facebook',
      'instagram_url' => 'lucide-instagram',
      'youtube_url' => 'lucide-youtube',
      'tiktok_url' => 'ri-tiktok-line',
      'twitter_url' => 'ri-twitter-x-line',
      'snapchat_url' => 'ri-snapchat-line',
  ];
@endphp

<footer class="bg-secondary text-neutral-300">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
      <div>
        <h3 class="mb-4 font-serif text-lg">
          {{ $section->settings->heading ?? config('app.name') }}
        </h3>
        <p class="text-sm text-neutral-300">
          {{ $section->settings->description }}
        </p>
      </div>

      @foreach ($getLinks() as $linksGroup)
        <div>
          <h4 class="mb-4 font-semibold">{{ $linksGroup['group'] }}</h4>
          <ul class="space-y-2 text-neutral-200">
            @foreach ($linksGroup['links'] as $item)
              <li><a class="hover:text-neutral-400" href="{{ $item['url'] }}">{{ $item['text'] }}</a></li>
            @endforeach
          </ul>
        </div>
      @endforeach
    </div>

    <div class="border-secondary-400 mt-12 border-t pt-8">
      <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
        <p class="text-sm text-neutral-300">
          © {{ now()->year }} {{ config('app.name') }}. All rights reserved.
        </p>

        @if ($section->settings->show_social_links)
          <div class="flex space-x-4">
            @foreach ($socials as $key => $icon)
              @if ($theme->settings->get($key))
                <a href="{{ $theme->settings->get($key) }}" aria-label="{{ $key }}"
                  class="text-neutral-300 hover:text-neutral-400">
                  @svg($icon, ['class' => 'h-5 w-5'])
                </a>
              @endif
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>

</footer>

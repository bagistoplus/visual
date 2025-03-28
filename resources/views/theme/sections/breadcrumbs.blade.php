@unless ($breadcrumbs->isEmpty())
  <div class="bg-surface border-b border-neutral-100">
    <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
      <nav class="flex items-center text-sm" aria-label="breadcrumbs">
        @foreach ($breadcrumbs as $breadcrumb)
          @if ($breadcrumb->url && !$loop->last)
            <a class="hover:text-primary transition-colors" href="{{ $breadcrumb->url }}">
              {{ $breadcrumb->title }}
            </a>
            <x-lucide-chevron-right class="mx-2 h-4 w-4" />
          @else
            <span class="text-primary truncate font-medium">
              {{ $breadcrumb->title }}
            </span>
          @endif
        @endforeach
      </nav>
    </div>
  </div>
@endunless

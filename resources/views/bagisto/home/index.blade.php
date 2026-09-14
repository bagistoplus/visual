{{--
    The home page of a theme whose sections Bagisto stores, with the hardcoded
    list of section types replaced by the view each type names for itself.

    Prepended to the shop namespace only for a theme edited in Bagisto mode, and
    deleted once Bagisto dispatches sections itself.
--}}
@php
    $channel = core()->getCurrentChannel();
    $sectionTypes = app(BagistoPlus\Visual\Support\SectionSystem::class);
    $blockIds = app(BagistoPlus\Visual\Support\SectionBlockIds::class);
    $editing = craftile()->inPreview();
@endphp

<!-- SEO Meta Content -->
@push ('meta')
    <meta
        name="title"
        content="{{ $channel->home_seo['meta_title'] ?? '' }}"
    />

    <meta
        name="description"
        content="{{ $channel->home_seo['meta_description'] ?? '' }}"
    />

    <meta
        name="keywords"
        content="{{ $channel->home_seo['meta_keywords'] ?? '' }}"
    />
@endPush

@push('scripts')
    @if(! empty($categories))
        <script>
            localStorage.setItem('categories', JSON.stringify(@json($categories)));
        </script>
    @endif
@endpush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{  $channel->home_seo['meta_title'] ?? '' }}
    </x-slot>

    @foreach ($sections as $section)
        @php
            $data = $section->options;
            $type = $sectionTypes->type($section->theme_code, $section->type);
            $inLayout = $type?->rendersInLayout ?? false;
        @endphp

        @continue (! $type)

        {{--
            The visual editor selects a section by the block it holds it as.
            Bagisto's own appearance editor marks it its own way, and the two
            never appear together: whichever editor is driving marks the page.
        --}}
        @if ($editing && ! $inLayout)
            <div data-block="{{ $blockIds->section($section->id) }}">
        @elseif (($preview ?? false) && ! $inLayout)
            <div
                data-section-id="{{ $section->id }}"
                data-section-name="{{ $section->name }}"
            >
        @endif

        @includeIf($type->view, ['section' => $section, 'data' => $data])

        @if ((($editing || ($preview ?? false))) && ! $inLayout)
            </div>
        @endif
    @endforeach

    @if (! $editing && ($preview ?? false))
        @include('shop::home.preview-bridge')
    @endif
</x-shop::layouts>

<div data-test-accordion>
    <div data-test-accordion-header>
        {{ $header ?? '' }}
    </div>

    <div data-test-accordion-content>
        {{ $content ?? $slot }}
    </div>
</div>

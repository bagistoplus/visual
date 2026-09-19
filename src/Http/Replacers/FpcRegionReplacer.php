<?php

namespace BagistoPlus\Visual\Http\Replacers;

use BagistoPlus\Visual\Support\FpcRegion;
use Spatie\ResponseCache\Replacers\Replacer;
use Symfony\Component\HttpFoundation\Response;

/**
 * Empties every region before a page is cached, and rebuilds them for whoever
 * the cached copy is later served to.
 */
class FpcRegionReplacer implements Replacer
{
    public function __construct(private readonly FpcRegion $regions) {}

    public function prepareResponseToCache(Response $response): void
    {
        $this->rewrite($response, fn (string $content): string => $this->regions->strip($content));
    }

    public function replaceInCachedResponse(Response $response): void
    {
        $this->rewrite($response, fn (string $content): string => $this->regions->fill($content));
    }

    /**
     * @param  callable(string): string  $rewrite
     */
    private function rewrite(Response $response, callable $rewrite): void
    {
        $content = $response->getContent();

        if (! is_string($content) || ! $this->regions->present($content)) {
            return;
        }

        $response->setContent($rewrite($content));
    }
}

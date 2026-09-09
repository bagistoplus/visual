<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use DateTime;
use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheProfile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Disables the full page cache while the storefront is rendered for the theme editor.
 *
 * It wraps the configured profile instead of replacing it, so a store that ships its own profile
 * keeps its behaviour.
 */
class DesignModeAwareCacheProfile implements CacheProfile
{
    public function __construct(protected CacheProfile $profile) {}

    public function enabled(Request $request): bool
    {
        if (ThemeEditor::inDesignMode() || ThemeEditor::inPreviewMode()) {
            return false;
        }

        return $this->profile->enabled($request);
    }

    public function shouldCacheRequest(Request $request): bool
    {
        return $this->profile->shouldCacheRequest($request);
    }

    public function shouldCacheResponse(Response $response): bool
    {
        return $this->profile->shouldCacheResponse($response);
    }

    public function useCacheNameSuffix(Request $request): string
    {
        return $this->profile->useCacheNameSuffix($request);
    }

    /**
     * Lifetime accessor used by spatie/laravel-responsecache 8 and above.
     */
    public function cacheLifetimeInSeconds(Request $request): int
    {
        return $this->profile->cacheLifetimeInSeconds($request);
    }

    /**
     * Lifetime accessor used by spatie/laravel-responsecache 7, replaced by cacheLifetimeInSeconds() in 8.
     */
    public function cacheRequestUntil(Request $request): DateTime
    {
        return $this->profile->cacheRequestUntil($request);
    }
}

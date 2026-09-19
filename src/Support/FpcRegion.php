<?php

namespace BagistoPlus\Visual\Support;

use Closure;

/**
 * Marks the parts of a page that are specific to one visitor, so they can be
 * neutralised before the page enters Bagisto's full page cache and rebuilt for
 * whoever the cached copy is later served to.
 *
 * Bagisto keys a cached page on channel, locale, currency and the customer id,
 * so every guest shares one entry and a signed in customer keeps their own copy
 * for the whole cache lifetime. Anything personal rendered into that HTML is
 * therefore either handed to the wrong guest or frozen for the right one.
 *
 * A region is delimited by HTML comments rather than an attribute because the
 * markup it guards may be absent entirely (a badge is not rendered at a count
 * of zero), and because some of it lives inside attribute values such as Alpine
 * `x-data`, which an attribute marker could not reach.
 *
 * Not to be confused with a Craftile region, which is a template layout zone.
 */
class FpcRegion
{
    public const NAME = 'visual-fpc';

    public const PREFIX = '<!--'.self::NAME.':';

    /**
     * One region, capturing its key. The backreference keeps the opening and
     * closing markers paired, so an unbalanced marker cannot swallow the rest
     * of the document.
     */
    public const PATTERN = '/<!--'.self::NAME.':([a-z0-9-]+)-->.*?<!--\/'.self::NAME.':\1-->/s';

    /** @var array<string, Closure(): string> */
    private array $resolvers = [];

    /** @var array<string, string> */
    private array $rendered = [];

    /** @var array<string, true> */
    private array $emitted = [];

    /** @var list<string> */
    private array $open = [];

    public function wrap(string $key, string $html): string
    {
        return self::open($key).$html.self::close($key);
    }

    /**
     * The same, for a region a page needs once however many blocks ask for it.
     *
     * Blade's `@once` cannot do this, because the view factory forgets what it
     * has rendered once each time the render stack empties, and a block is its
     * own top level render. The closure keeps the payload unbuilt for every
     * caller after the first.
     *
     * @param  Closure(): string  $html
     */
    public function wrapOnce(string $key, Closure $html): string
    {
        return $this->claim($key) ? $this->wrap($key, $html()) : '';
    }

    /**
     * Start capturing a region, for the `@fpcRegion` directive.
     *
     * The keys are a stack so that a region opened inside another, which an
     * included partial can do without meaning to, does not take the outer one's
     * key with it when it closes.
     */
    public function begin(string $key): void
    {
        $this->open[] = $key;

        ob_start();
    }

    /**
     * The same, answering whether this caller is the first to ask for the key.
     *
     * Nothing is captured when it is not, which is what lets `@fpcRegionOnce`
     * skip its body rather than build it and throw it away.
     */
    public function beginOnce(string $key): bool
    {
        if (! $this->claim($key)) {
            return false;
        }

        $this->begin($key);

        return true;
    }

    public function end(): string
    {
        return $this->wrap((string) array_pop($this->open), (string) ob_get_clean());
    }

    /**
     * Take the key for this page, answering whether it was still going.
     *
     * Separate from `begin()` so that `wrapOnce()` does not open a buffer it
     * has no `end()` to close.
     */
    private function claim(string $key): bool
    {
        if (isset($this->emitted[$key])) {
            return false;
        }

        return $this->emitted[$key] = true;
    }

    /**
     * @param  Closure(): string  $resolver
     */
    public function resolve(string $key, Closure $resolver): void
    {
        $this->resolvers[$key] = $resolver;
    }

    /**
     * Rebuild one region for the current visitor. Memoised, because a page may
     * carry the same region more than once: a mini cart and a cart drawer both
     * render a cart badge.
     */
    public function render(string $key): string
    {
        if (array_key_exists($key, $this->rendered)) {
            return $this->rendered[$key];
        }

        $resolver = $this->resolvers[$key] ?? null;

        return $this->rendered[$key] = $resolver ? ($resolver)() : '';
    }

    /**
     * Strip every region's content, leaving the markers. Used on the copy that
     * is about to be cached.
     */
    public function strip(string $content): string
    {
        return (string) preg_replace(
            self::PATTERN,
            '<!--'.self::NAME.':$1--><!--/'.self::NAME.':$1-->',
            $content
        );
    }

    /**
     * Refill every region from its resolver. Used on a cached copy that is
     * about to be sent.
     */
    public function fill(string $content): string
    {
        return (string) preg_replace_callback(
            self::PATTERN,
            fn (array $matches): string => $this->wrap($matches[1], $this->render($matches[1])),
            $content
        );
    }

    public function present(string $content): bool
    {
        return str_contains($content, self::PREFIX);
    }

    private static function open(string $key): string
    {
        return '<!--'.self::NAME.':'.$key.'-->';
    }

    private static function close(string $key): string
    {
        return '<!--/'.self::NAME.':'.$key.'-->';
    }
}

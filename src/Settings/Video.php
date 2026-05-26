<?php

namespace BagistoPlus\Visual\Settings;

use Illuminate\Support\Str;
use InvalidArgumentException;

class Video extends Base
{
    protected static string $type = 'video';

    public function __construct(string $id, string $label, array $meta = [])
    {
        parent::__construct($id, $label, array_merge([
            'acceptsExternal' => false,
            'externalSources' => [],
        ], $meta));
    }

    public function acceptsExternal(array $sources = ['youtube', 'vimeo']): self
    {
        if ($sources === []) {
            throw new InvalidArgumentException('Video external sources cannot be empty.');
        }

        $this->meta['acceptsExternal'] = true;
        $this->meta['externalSources'] = collect($sources)
            ->map(fn ($source) => $this->normalizeExternalSource($source))
            ->values()
            ->all();

        return $this;
    }

    private function normalizeExternalSource(string|array $source): array
    {
        if (is_string($source)) {
            return match ($source) {
                'youtube' => ['host' => 'youtube', 'label' => 'YouTube', 'kind' => 'embed'],
                'vimeo' => ['host' => 'vimeo', 'label' => 'Vimeo', 'kind' => 'embed'],
                default => throw new InvalidArgumentException("Unsupported video source [{$source}]."),
            };
        }

        $host = $source['host'] ?? null;
        $pattern = $source['pattern'] ?? null;

        if (! is_string($host) || ! preg_match('/^[a-z][a-z0-9_]*$/', $host)) {
            throw new InvalidArgumentException('Custom video sources require a valid slug-like host.');
        }

        if (! is_string($pattern) || $pattern === '') {
            throw new InvalidArgumentException("Custom video source [{$host}] requires a PHP regex pattern.");
        }

        if (@preg_match($pattern, '') === false) {
            throw new InvalidArgumentException("Custom video source [{$host}] has an invalid PHP regex pattern.");
        }

        [$jsPattern, $jsFlags] = $this->phpRegexToJavaScript($pattern, $host);

        return [
            'host' => $host,
            'label' => $source['label'] ?? $this->labelFromHost($host),
            'kind' => 'video',
            'pattern' => $pattern,
            'jsPattern' => $jsPattern,
            'jsFlags' => $jsFlags,
        ];
    }

    private function phpRegexToJavaScript(string $pattern, string $host): array
    {
        $parsed = $this->parsePhpRegex($pattern);

        if (! $parsed) {
            throw new InvalidArgumentException("Custom video source [{$host}] must use a delimited PHP regex.");
        }

        [$body, $flags] = $parsed;
        $unsupportedFlags = preg_replace('/[imsu]/', '', $flags);

        if ($unsupportedFlags !== '') {
            throw new InvalidArgumentException("Custom video source [{$host}] uses unsupported regex flags [{$unsupportedFlags}].");
        }

        if (preg_match('/\(\?<[!=]|\(\?\(|\(\?P[<=>]|\\\\K/', $body)) {
            throw new InvalidArgumentException("Custom video source [{$host}] uses regex features that cannot be converted to JavaScript.");
        }

        return [$body, $flags];
    }

    private function parsePhpRegex(string $pattern): ?array
    {
        $delimiter = $pattern[0] ?? null;

        if (! $delimiter || ctype_alnum($delimiter) || $delimiter === '\\') {
            return null;
        }

        $length = strlen($pattern);
        $closing = null;

        for ($i = $length - 1; $i > 0; $i--) {
            if ($pattern[$i] !== $delimiter || $this->isEscaped($pattern, $i)) {
                continue;
            }

            $closing = $i;
            break;
        }

        if ($closing === null) {
            return null;
        }

        return [
            substr($pattern, 1, $closing - 1),
            substr($pattern, $closing + 1),
        ];
    }

    private function isEscaped(string $value, int $position): bool
    {
        $slashes = 0;

        for ($i = $position - 1; $i >= 0 && $value[$i] === '\\'; $i--) {
            $slashes++;
        }

        return $slashes % 2 === 1;
    }

    private function labelFromHost(string $host): string
    {
        if (strlen($host) <= 3) {
            return strtoupper($host);
        }

        return Str::headline($host);
    }
}

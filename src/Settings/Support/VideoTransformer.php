<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\Visual;
use Illuminate\Support\Facades\Storage;

class VideoTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, array $schema = []): ?VideoValue
    {
        $url = $this->url($value);
        $path = $this->path($value);

        if (! $url && ! $path) {
            return null;
        }

        $input = $url ?: $path;

        if (! is_string($input) || $input === '') {
            return null;
        }

        if ($youtube = $this->youtube($input)) {
            return $youtube;
        }

        if ($vimeo = $this->vimeo($input)) {
            return $vimeo;
        }

        if ($custom = $this->custom($input, $schema)) {
            return $custom;
        }

        if ($this->isWebPlayable($input)) {
            return $this->native($input, $this->host($value));
        }

        if (! filter_var($input, FILTER_VALIDATE_URL)) {
            return $this->native($input);
        }

        return null;
    }

    private function native(string $pathOrUrl, ?string $host = null): VideoValue
    {
        $isUrl = filter_var($pathOrUrl, FILTER_VALIDATE_URL);
        $url = $isUrl ? $pathOrUrl : Storage::disk(Visual::videosDisk())->url($pathOrUrl);

        return new VideoValue(
            media_type: 'video',
            host: $host,
            url: $url,
            original_url: $url,
            path: $isUrl ? null : $pathOrUrl,
            name: $isUrl ? null : $this->name($pathOrUrl),
            sources: [[
                'url' => $url,
                'mime_type' => $this->mimeType($pathOrUrl),
            ]],
        );
    }

    private function youtube(string $url): ?VideoValue
    {
        $id = $this->youtubeId($url);

        if (! $id) {
            return null;
        }

        return new VideoValue(
            media_type: 'external_video',
            host: 'youtube',
            url: $this->youtubeEmbedUrl($url, $id),
            original_url: $url,
            preview_image: new ImageValue(
                name: 'YouTube thumbnail',
                path: "https://img.youtube.com/vi/{$id}/hqdefault.jpg",
                url: "https://img.youtube.com/vi/{$id}/hqdefault.jpg",
            ),
            external_id: $id,
        );
    }

    private function vimeo(string $url): ?VideoValue
    {
        $data = $this->vimeoData($url);

        if (! $data) {
            return null;
        }

        return new VideoValue(
            media_type: 'external_video',
            host: 'vimeo',
            url: $this->vimeoEmbedUrl($url, $data['id'], $data['hash']),
            original_url: $url,
            external_id: $data['id'],
        );
    }

    private function custom(string $url, array $schema): ?VideoValue
    {
        foreach ($schema['externalSources'] ?? [] as $source) {
            if (($source['kind'] ?? null) !== 'video' || ! isset($source['pattern'], $source['host'])) {
                continue;
            }

            if (@preg_match($source['pattern'], $url) === 1) {
                return $this->native($url, $source['host']);
            }
        }

        return null;
    }

    private function youtubeId(string $url): ?string
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');

        if ($host === 'youtu.be') {
            return $this->validYoutubeId(explode('/', $path)[0] ?? null);
        }

        if (! str_contains($host, 'youtube.com')) {
            return null;
        }

        if ($path === 'watch') {
            parse_str($parts['query'] ?? '', $query);

            return $this->validYoutubeId($query['v'] ?? null);
        }

        if (preg_match('~^(embed|shorts)/([^/?#]+)~', $path, $matches)) {
            return $this->validYoutubeId($matches[2]);
        }

        return null;
    }

    private function validYoutubeId(mixed $id): ?string
    {
        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{6,}$/', $id) ? $id : null;
    }

    private function youtubeEmbedUrl(string $url, string $id): string
    {
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);

        $params = [];

        foreach (['end', 'list', 'index', 'autoplay', 'controls', 'mute', 'loop', 'playlist', 'playsinline'] as $key) {
            if (isset($query[$key]) && is_scalar($query[$key])) {
                $params[$key] = (string) $query[$key];
            }
        }

        $start = $query['start'] ?? $query['t'] ?? null;
        $start = $this->seconds($start);

        if ($start !== null) {
            $params['start'] = (string) $start;
        }

        return 'https://www.youtube.com/embed/'.$id.($params ? '?'.http_build_query($params) : '');
    }

    private function vimeoData(string $url): ?array
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');

        if (! str_contains($host, 'vimeo.com')) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $path)));
        $id = null;
        $hash = null;

        foreach ($segments as $index => $segment) {
            if (preg_match('/^\d+$/', $segment)) {
                $id = $segment;
                $next = $segments[$index + 1] ?? null;

                if (is_string($next) && preg_match('/^[A-Za-z0-9]+$/', $next)) {
                    $hash = $next;
                }
            }
        }

        if (! $id) {
            return null;
        }

        parse_str($parts['query'] ?? '', $query);

        if (isset($query['h']) && is_scalar($query['h'])) {
            $hash = (string) $query['h'];
        }

        return ['id' => $id, 'hash' => $hash];
    }

    private function vimeoEmbedUrl(string $url, string $id, ?string $hash): string
    {
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);

        $params = [];

        foreach (['autoplay', 'muted', 'loop', 'controls', 'background', 'color'] as $key) {
            if (isset($query[$key]) && is_scalar($query[$key])) {
                $params[$key] = (string) $query[$key];
            }
        }

        if ($hash) {
            $params['h'] = $hash;
        }

        $embed = 'https://player.vimeo.com/video/'.$id.($params ? '?'.http_build_query($params) : '');

        if (isset($parts['fragment']) && str_starts_with($parts['fragment'], 't=')) {
            $embed .= '#'.$parts['fragment'];
        }

        return $embed;
    }

    private function seconds(mixed $value): ?int
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = (string) $value;

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (! preg_match('/^(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s?)?$/', $value, $matches)) {
            return null;
        }

        return ((int) ($matches[1] ?? 0) * 3600)
            + ((int) ($matches[2] ?? 0) * 60)
            + (int) ($matches[3] ?? 0);
    }

    private function path(mixed $value): ?string
    {
        if (is_string($value) && ! filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (data_get($value, 'mode') === 'external') {
            return null;
        }

        if (data_get($value, 'mode') === 'upload') {
            $retainedPath = data_get($value, 'upload.path');

            if (is_string($retainedPath)) {
                return $retainedPath;
            }
        }

        $path = data_get($value, 'path');

        return is_string($path) ? $path : null;
    }

    private function url(mixed $value): ?string
    {
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (data_get($value, 'mode') === 'upload') {
            return null;
        }

        if (data_get($value, 'mode') === 'external') {
            $retainedUrl = data_get($value, 'external.url');

            if (is_string($retainedUrl)) {
                return $retainedUrl;
            }
        }

        $url = data_get($value, 'url');

        return is_string($url) ? $url : null;
    }

    private function host(mixed $value): ?string
    {
        if (data_get($value, 'mode') === 'external') {
            $retainedHost = data_get($value, 'external.host');

            if (is_string($retainedHost)) {
                return $retainedHost;
            }
        }

        $host = data_get($value, 'host');

        return is_string($host) ? $host : null;
    }

    private function name(string $path): string
    {
        [$encodedName] = explode('_', pathinfo($path, PATHINFO_FILENAME));

        if (ctype_xdigit($encodedName) && strlen($encodedName) % 2 === 0) {
            return hex2bin($encodedName) ?: $encodedName;
        }

        return $encodedName;
    }

    private function isWebPlayable(string $url): bool
    {
        return $this->mimeType($url) !== null;
    }

    private function mimeType(string $url): ?string
    {
        return match (strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION))) {
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg', 'ogv' => 'video/ogg',
            default => null,
        };
    }
}

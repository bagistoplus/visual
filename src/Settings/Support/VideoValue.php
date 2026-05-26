<?php

namespace BagistoPlus\Visual\Settings\Support;

use Illuminate\Support\HtmlString;
use Illuminate\View\ComponentAttributeBag;

class VideoValue
{
    public function __construct(
        public string $media_type,
        public ?string $host,
        public string $url,
        public string $original_url,
        public ?string $path = null,
        public ?string $name = null,
        public array $sources = [],
        public ?ImageValue $preview_image = null,
        public ?string $external_id = null,
    ) {}

    public function __toString(): string
    {
        return $this->url;
    }

    public function isUploaded(): bool
    {
        return $this->media_type === 'video' && $this->path !== null;
    }

    public function isExternal(): bool
    {
        return $this->media_type === 'external_video';
    }

    public function render(array $attributes = []): HtmlString
    {
        return $this->isExternal()
            ? $this->renderExternal($attributes)
            : $this->renderNative($attributes);
    }

    private function renderNative(array $attributes): HtmlString
    {
        $sourceAttributes = $attributes['source_attributes'] ?? [];
        unset($attributes['source_attributes']);

        $attributeBag = (new ComponentAttributeBag($attributes))->merge([
            'controls' => true,
            'playsinline' => true,
            'preload' => 'metadata',
        ]);

        $sources = collect($this->sources ?: [['url' => $this->url, 'mime_type' => $this->mimeType($this->url)]])
            ->map(function ($source) use ($sourceAttributes) {
                $sourceBag = new ComponentAttributeBag(array_merge($sourceAttributes, [
                    'src' => $source['url'],
                ]));

                if (! empty($source['mime_type'])) {
                    $sourceBag = $sourceBag->merge(['type' => $source['mime_type']]);
                }

                return '<source '.$sourceBag->toHtml().'>';
            })
            ->implode('');

        return new HtmlString('<video '.$attributeBag->toHtml().'>'.$sources.'</video>');
    }

    private function renderExternal(array $attributes): HtmlString
    {
        [$url, $attributes] = $this->applyPlayerAttributes($attributes);

        $attributeBag = (new ComponentAttributeBag($attributes))->merge([
            'src' => $url,
            'title' => $this->host === 'vimeo' ? 'Vimeo video player' : 'YouTube video player',
            'loading' => 'lazy',
            'allow' => 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture',
            'allowfullscreen' => true,
        ]);

        return new HtmlString('<iframe '.$attributeBag->toHtml().'></iframe>');
    }

    private function applyPlayerAttributes(array $attributes): array
    {
        $playerKeys = ['autoplay', 'muted', 'controls', 'loop', 'playsinline'];
        $params = [];

        foreach ($playerKeys as $key) {
            if (! array_key_exists($key, $attributes)) {
                continue;
            }

            $value = filter_var($attributes[$key], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            unset($attributes[$key]);

            if ($value === null) {
                continue;
            }

            if ($this->host === 'youtube') {
                $params = array_merge($params, $this->youtubePlayerParam($key, $value));
            } elseif ($this->host === 'vimeo') {
                $params = array_merge($params, $this->vimeoPlayerParam($key, $value));
            }
        }

        return [$this->mergeQuery($this->url, $params), $attributes];
    }

    private function youtubePlayerParam(string $key, bool $value): array
    {
        return match ($key) {
            'autoplay' => ['autoplay' => $value ? '1' : '0'],
            'muted' => ['mute' => $value ? '1' : '0'],
            'controls' => ['controls' => $value ? '1' : '0'],
            'playsinline' => ['playsinline' => $value ? '1' : '0'],
            'loop' => $value ? ['loop' => '1', 'playlist' => $this->external_id] : ['loop' => '0'],
            default => [],
        };
    }

    private function vimeoPlayerParam(string $key, bool $value): array
    {
        return match ($key) {
            'autoplay', 'muted', 'controls', 'loop' => [$key => $value ? '1' : '0'],
            default => [],
        };
    }

    private function mergeQuery(string $url, array $params): string
    {
        if ($params === []) {
            return $url;
        }

        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);

        $query = array_merge($query, array_filter($params, fn ($value) => $value !== null));
        $rebuilt = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '');

        if (isset($parts['path'])) {
            $rebuilt .= $parts['path'];
        }

        if ($query !== []) {
            $rebuilt .= '?'.http_build_query($query);
        }

        if (isset($parts['fragment'])) {
            $rebuilt .= '#'.$parts['fragment'];
        }

        return $rebuilt;
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

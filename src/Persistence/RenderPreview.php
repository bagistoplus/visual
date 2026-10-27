<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\Facades\Craftile;
use Craftile\Laravel\View\JsonViewParser;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RenderPreview
{
    /**
     * Render a preview by making a sub-request with design mode enabled.
     *
     * @param  string  $url  The URL to render
     * @param  array|null  $blockIds  Optional array of block IDs to render (null = render all)
     * @param  string|null  $channel  The channel the edit was made in
     * @param  string|null  $locale  The locale the edit was made in
     */
    public function execute(string $url, ?array $blockIds = null, ?string $channel = null, ?string $locale = null): string|RedirectResponse
    {
        $baseUrl = rtrim(config('app.url'));
        $basePath = parse_url($baseUrl, PHP_URL_PATH);

        $url = $this->withQuery($url, [
            'channel' => $channel,
            'locale' => $locale,
        ]);

        // Store block IDs in session with unique key if provided
        if ($blockIds !== null && count($blockIds) > 0) {
            $key = Str::random(16);
            session()->put("visual.render.{$key}", $blockIds);

            $url = $this->withQuery($url, ['_visual_render' => $key]);
        }
        // Handle subdirectory installs by redirecting
        if ($basePath !== null) {
            return redirect($url);
        }

        // Reset Craftile's preview mode cache before sub-request
        Craftile::detectPreviewUsing(function () {
            return ThemeEditor::inDesignMode();
        });

        app(JsonViewParser::class)->clearCache();
        app(ThemeSettingsLoader::class)->clearCache();

        // Persist any pending writes (e.g. visual.render.{$key}) to disk so
        // the sub-request's session start reads them.
        session()->save();

        $request = Request::create($url, 'GET');
        $request->cookies->set(
            config('session.cookie'),
            $this->encryptSessionCookie(session()->getId())
        );
        $response = app()->handle($request);

        return $response->getContent();
    }

    /**
     * Append query parameters to a url, skipping empty ones. Appended last, so a
     * parameter the url already carries is overridden.
     *
     * @param  array<string, string|null>  $params
     */
    protected function withQuery(string $url, array $params): string
    {
        $params = array_filter($params);

        if ($params === []) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query($params);
    }

    /**
     * Encrypt a session id the way EncryptCookies expects, so the sub-request
     * decrypts back to the same id and StartSession joins the existing session
     * instead of allocating a fresh one.
     */
    protected function encryptSessionCookie(string $sessionId): string
    {
        $cookieName = config('session.cookie');
        $encrypter = app(Encrypter::class);

        return $encrypter->encrypt(
            CookieValuePrefix::create($cookieName, $encrypter->getKey()).$sessionId,
            EncryptCookies::serialized($cookieName)
        );
    }
}

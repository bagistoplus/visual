<?php

namespace BagistoPlus\Visual\Middlewares;

use Closure;
use Illuminate\Http\Request;

/**
 * In the editor and the previews, every generated route URL carries the theme in its query
 * string. Code that appends its own query to such a URL with "?" (Bagisto's Stripe success
 * and cancel URLs do) produces "?_draftPreview=theme?session_id=…", which PHP reads as a
 * single preview value. This splits that value back into the theme and the appended params.
 */
class RepairPreviewQueryString
{
    protected const PREVIEW_KEYS = ['_designMode', '_draftPreview', '_previewMode'];

    public function handle(Request $request, Closure $next)
    {
        $repaired = false;

        foreach (self::PREVIEW_KEYS as $key) {
            $value = $request->query->get($key);

            if (! is_string($value) || ! str_contains($value, '?')) {
                continue;
            }

            [$theme, $appendedQuery] = explode('?', $value, 2);

            $request->query->set($key, $theme);

            parse_str($appendedQuery, $appendedParams);

            foreach ($appendedParams as $name => $param) {
                if (! $request->query->has($name)) {
                    $request->query->set($name, $param);
                }
            }

            $repaired = true;
        }

        if ($repaired) {
            $request->server->set('QUERY_STRING', http_build_query($request->query->all()));
        }

        return $next($request);
    }
}

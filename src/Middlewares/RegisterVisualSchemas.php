<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Theme\Theme as VisualTheme;
use Closure;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Http\Request;

class RegisterVisualSchemas
{
    public function handle(Request $request, Closure $next)
    {
        $theme = themes()->current();

        if ($theme instanceof VisualTheme && $theme->isVisualTheme()) {
            Craftile::registerDiscoveredSchemas();
        }

        return $next($request);
    }
}

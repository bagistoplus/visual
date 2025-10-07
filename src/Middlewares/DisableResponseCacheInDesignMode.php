<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Closure;
use Illuminate\Http\Request;

class DisableResponseCacheInDesignMode
{
    public function handle(Request $request, Closure $next)
    {
        if (ThemeEditor::inDesignMode() || ThemeEditor::inPreviewMode()) {
            config(['responsecache.enabled' => false]);
        }

        return $next($request);
    }
}

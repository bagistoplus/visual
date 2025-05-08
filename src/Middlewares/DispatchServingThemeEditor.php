<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Events\ServingThemeEditor;
use Illuminate\Http\Request;

class DispatchServingThemeEditor
{
    public function handle(Request $request, \Closure $next)
    {
        if ($request->is('*/visual/editor/*') && ! $request->is('*/visual/editor/api*')) {
            ServingThemeEditor::dispatch($request);
        }

        return $next($request);
    }
}

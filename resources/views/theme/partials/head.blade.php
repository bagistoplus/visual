<title>@yield('title', config('app.name'))</title>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="content-language" content="{{ app()->getLocale() }}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="currency" content="{{ core()->getCurrentCurrency()->toJson() }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

@stack('meta')

{{-- <link rel="icon" sizes="16x16" href="{{ core()->getCurrentChannel()->favicon_url ?? bagisto_asset('images/favicon.ico') }}" /> --}}

{{-- blade-formatter-disable --}}
{{
    Vite::useHotFile('vendor/bagistoplus/visual/shop.hot')
      ->useBuildDirectory('vendor/bagistoplus/visual/shop')
      ->withEntryPoints([
        'resources/assets/shop/css/shop.css',
        'resources/assets/shop/ts/index.ts'
      ])
}}
{{-- blade-formatter-enable --}}

@stack('styles')

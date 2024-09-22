<?php

namespace BagistoPlus\Visual\Providers;

use Illuminate\Support\AggregateServiceProvider;

class VisualServiceProvider extends AggregateServiceProvider
{
    protected $providers = [
        CoreServiceProvider::class,
        ViewServiceProvider::class,
    ];
}

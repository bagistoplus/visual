<?php

namespace BagistoPlus\Visual\Providers;

use Illuminate\Support\AggregateServiceProvider;

class VisualServiceProvider extends AggregateServiceProvider
{
    protected $providers = [
        ViewServiceProvider::class,
        CoreServiceProvider::class,
        AdminServiceProvider::class,
    ];
}

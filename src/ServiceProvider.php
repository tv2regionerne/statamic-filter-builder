<?php

namespace Tv2regionerne\StatamicFilterBuilder;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        Fieldtypes\FilterBuilder::class,
    ];

    protected $scopes = [
        Scopes\Filters::class,
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
        ],
    ];
}

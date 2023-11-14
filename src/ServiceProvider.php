<?php

namespace Tv2regionerne\StatamicFilterBuilder;

use Illuminate\Support\Facades\Validator;
use Statamic\Facades\Antlers;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        Fieldtypes\FilterBuilder::class,
    ];

    protected $scopes = [
        Scopes\FilterBuilder::class,
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
        ],
    ];

    public function bootAddon()
    {
        Validator::extend('filter_builder_fieldtype_variables', function ($attribute, $value, $parameters) {
            foreach ($value as $variable) {
                try {
                    Antlers::parse($variable, []);
                } catch (\Exception $e) {
                    return false;
                }
            }

            return true;
        });
    }
}

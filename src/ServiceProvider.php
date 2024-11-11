<?php

namespace Tv2regionerne\StatamicFilterBuilder;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Tags\Collection\Collection as CollectionTag;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        Fieldtypes\FilterBuilder::class,
        Fieldtypes\SortBuilder::class,
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
        $this->addCollectionHook();
    }

    private function addCollectionHook(): self
    {
        CollectionTag::hook('init', function ($value, $next) {
            if (! $this->params->get('filter_builder')) {
                return $next($value);
            }

            if ($this->params->get('query_scope')) {
                return $next($value);
            }

            $this->params = $this->params->merge([
                'query_scope' => 'filter_builder',
            ]);

            return $next($value);
        });

        return $this;
    }
}

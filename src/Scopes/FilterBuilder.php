<?php

namespace Tv2regionerne\StatamicFilterBuilder\Scopes;

use Statamic\Facades\Cascade;
use Statamic\Query\Scopes\Scope;
use Statamic\Support\Str;
use Tv2regionerne\StatamicFilterBuilder\VariableParser;

class FilterBuilder extends Scope
{
    public function apply($query, $values)
    {
        $filters = $values['filter_builder'] ?? [];

        foreach ($filters as $filter) {
            $handle = $filter['handle'];
            $operator = $filter['values']['operator'];
            $values = $filter['values']['values'];
            $variables = $filter['values']['variables'];

            $cascade = Cascade::toArray();
            foreach ($variables as $variable) {
                if (! $parsed = VariableParser::parse($variable, $cascade)) {
                    continue;
                }
                $values = array_merge($values, $parsed);
            }

            $query->where(function ($query) use ($handle, $operator, $values) {
                foreach ($values as $i => $value) {
                    if ($operator === 'like') {
                        $value = Str::ensureLeft($value, '%');
                        $value = Str::ensureRight($value, '%');
                    }
                    $method = $i ? 'orWhere' : 'where';
                    $query->{$method}($handle, $operator, $value);
                }
            });
        }
    }
}

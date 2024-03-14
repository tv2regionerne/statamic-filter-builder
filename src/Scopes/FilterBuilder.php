<?php

namespace Tv2regionerne\StatamicFilterBuilder\Scopes;

use Statamic\Facades\Cascade;
use Statamic\Facades\Collection;
use Statamic\Fields\Field;
use Statamic\Query\Scopes\Scope;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Tv2regionerne\StatamicFilterBuilder\VariableParser;

class FilterBuilder extends Scope
{
    public function apply($query, $values)
    {
        $fields = $this->fields(explode('|', $values['from']));
        $filters = $values['filter_builder'] ?? [];

        foreach ($filters as $filter) {
            $handle = $filter['handle'];
            $operator = $filter['values']['operator'];
            $values = $filter['values']['values'] ?? [];
            $variables = $filter['values']['variables'];

            $field = $fields[$handle];
            $json = in_array($field->type(), ['entries', 'terms', 'users']) && $field->get('max_items', 0) !== 1;

            $cascade = Cascade::toArray();
            foreach ($variables as $variable) {
                if (! $parsed = VariableParser::parse($variable, $cascade)) {
                    continue;
                }
                $values = array_merge($values, $parsed);
            }

            // If we have no values, ignore the filter
            if (! $values) {
                continue;
            }

            $query->where(function ($query) use ($json, $handle, $operator, $values) {
                foreach ($values as $i => $value) {
                    if ($json) {
                        $method = $operator === '='
                            ? ($i ? 'orWhereJsonContains' : 'whereJsonContains')
                            : ($i ? 'orWhereJsonDoesntContain' : 'whereJsonDoesntContain');
                        $query->{$method}($handle, $value);
                    } else {
                        if ($operator === 'like') {
                            $value = Str::ensureLeft($value, '%');
                            $value = Str::ensureRight($value, '%');
                        }
                        $method = $i ? 'orWhere' : 'where';
                        $query->{$method}($handle, $operator, $value);
                    }
                }
            });
        }
    }

    protected function fields($collections)
    {
        return collect([
            'id' => new Field('id', [
                'display' => 'ID',
                'type' => 'text',
            ]),
        ])->merge(collect(Arr::wrap($collections))
            ->flatMap(function ($collection) {
                return Collection::findByHandle($collection)->entryBlueprints();
            })
            ->flatMap(function ($blueprint) {
                return $blueprint
                    ->fields()
                    ->all()
                    ->filter->isFilterable();
            }));
    }
}

<?php

namespace Tv2regionerne\StatamicFilterBuilder\Fieldtypes\Concerns;

use Facades\Statamic\Fieldtypes\RowId;
use Statamic\Facades\Collection;
use Statamic\Fields\Field;
use Statamic\Support\Arr;

trait UsesFields
{
    protected $fields;

    protected $singleTypes = [
        'toggle',
        'date',
    ];

    public function process($data)
    {
        return collect($data)->map(function ($item) {
            $item['id'] = $item['id'] ?? RowId::generate();
            $fields = $this->getItemFields($item);
            $values = $item['values'];
            $values = $fields
                ->addValues($values)
                ->process()
                ->values()
                ->all();
            if ($fields->has('values') && in_array($fields->get('values')->type(), $this->singleTypes)) {
                $values['values'] = [$values['values']];
            }
            $item['values'] = $values;

            return $item;
        })->all();
    }

    public function preProcess($data)
    {
        return collect($data)->map(function ($item) {
            $item['id'] = $item['id'] ?? RowId::generate();
            $fields = $this->getItemFields($item);
            $values = $item['values'];
            if ($fields->has('values') && in_array($fields->get('values')->type(), $this->singleTypes)) {
                $values['values'] = $values['values'][0];
            }
            $values = $fields
                ->addValues($values)
                ->preProcess()
                ->values()
                ->all();
            $item['values'] = $values;

            return $item;
        })->all();
    }

    public function preProcessValidatable($data)
    {
        return collect($data)->map(function ($item) {
            $fields = $this->getItemFields($item);
            $values = $item['values'];
            $processed = $fields
                ->addValues($item['values'])
                ->preProcessValidatables()
                ->values()
                ->all();
            $item['values'] = array_merge($values, $processed);

            return $item;
        })->all();
    }

    public function extraRules(): array
    {
        return collect($this->field->value())->map(function ($item, $index) {
            $prefix = $this->field->handle().'.'.$index.'.values';
            $fields = $this->getItemFields($item);
            $values = $item['values'];
            $rules = $fields
                ->addValues($values)
                ->validator()
                ->withContext([
                    'prefix' => $this->field->validationContext('prefix').$prefix.'.',
                ])
                ->rules();

            return collect($rules)
                ->mapWithKeys(function ($rules, $handle) use ($prefix) {
                    return [$prefix.'.'.$handle => $rules];
                })->all();
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->all();
    }

    public function extraValidationAttributes(): array
    {
        return collect($this->field->value())->map(function ($item, $index) {
            $prefix = $this->field->handle().'.'.$index.'.values';
            $fields = $this->getItemFields($item);
            $values = $item['values'];
            $attributes = $fields
                ->addValues($values)
                ->validator()
                ->attributes();

            return collect($attributes)
                ->mapWithKeys(function ($rules, $handle) use ($prefix) {
                    return [$prefix.'.'.$handle => $rules];
                })->all();
        })->reduce(function ($carry, $attributes) {
            return $carry->merge($attributes);
        }, collect())->all();
    }

    public function preload()
    {
        $fields = $this->getFields()->map(function ($field) {
            return [
                'handle' => $field->handle(),
                'display' => $field->display(),
                'type' => $field->type(),
                'fields' => $this->getFieldFields($field)->toPublishArray(),
            ];
        })->values();

        $existing = collect($this->field->value())->mapWithKeys(function ($item) {
            return [$item['id'] => $this->getItemFields($item)->addValues($item['values'])->meta()];
        })->toArray();

        $defaults = $this->getFields()->map(function ($field) {
            return $this->getFieldFields($field)->all()->map(function ($field) {
                return $field->fieldtype()->preProcess($field->defaultValue());
            })->all();
        })->all();

        $new = $this->getFields()->map(function ($field, $handle) use ($defaults) {
            return $this->getFieldFields($field)->addValues($defaults[$handle])->meta();
        })->toArray();

        return [
            'fields' => $fields,
            'existing' => $existing,
            'new' => $new,
            'defaults' => $defaults,
        ];
    }

    protected function getFields()
    {
        if ($this->fields) {
            return $this->fields;
        }

        $collections = $this->getCollections();

        $groups = collect(Arr::wrap($collections))
            ->mapWithKeys(function ($collection) {
                $fields = Collection::findByHandle($collection)->entryBlueprints()
                    ->flatMap(function ($blueprint) {
                        return $blueprint
                            ->fields()
                            ->all();
                    });

                return [$collection => $fields];
            });

        $handles = $groups
            ->flatMap(fn ($fields) => $fields->keys())
            ->unique();
        foreach ($groups as $fields) {
            $handles = $handles->intersect($fields->keys());
        }

        $this->fields = $groups
            ->flatMap(fn ($fields) => $fields)
            ->only($handles)
            ->merge([
                'id' => new Field('id', [
                    'display' => 'ID',
                    'type' => 'text',
                ]),
            ])
            ->sort(function ($a, $b) {
                return $a->display() <=> $b->display();
            });

        return $this->fields;
    }

    protected function getItemFields($item)
    {
        return $this->getFieldFields($this->getFields()[$item['handle']]);
    }

    protected function getCollections()
    {
        if ($this->config('mode', 'config') === 'config') {
            return $this->config('collections');
        }

        $key = $this->field->fieldPathKeys();
        array_splice($key, -1, 1, [$this->config('field')]);
        $key = implode('.', $key);

        return data_get($this->field->parent()->data(), $key);
    }
}

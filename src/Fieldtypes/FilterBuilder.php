<?php

namespace Tv2regionerne\StatamicFilterBuilder\Fieldtypes;

use Facades\Statamic\Fieldtypes\RowId;
use Statamic\Facades\Collection;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;
use Tv2regionerne\StatamicFilterBuilder\VariableParser;

class FilterBuilder extends Fieldtype
{
    protected $fields;

    protected $singleTypes = [
        'toggle',
        'date',
    ];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'mode' => [
                        'display' => __('Mode'),
                        'instructions' => __('The collection listing source'),
                        'type' => 'button_group',
                        'default' => 'config',
                        'options' => [
                            'config' => __('Field Configuration'),
                            'field' => __('Blueprint Field'),
                        ],
                    ],
                    'collections' => [
                        'display' => __('Collections'),
                        'instructions' => __('The filtered collections'),
                        'mode' => 'select',
                        'type' => 'collections',
                        'validate' => 'required_if:mode,config',
                        'if' => [
                            'mode' => 'config',
                        ],
                    ],
                    'field' => [
                        'display' => __('Field'),
                        'instructions' => __('The field listing the filtered collections'),
                        'type' => 'text',
                        'validate' => 'required_if:mode,field',
                        'if' => [
                            'mode' => 'field',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function process($data)
    {
        return collect($data)->map(function ($filter) {
            $filter['id'] = $filter['id'] ?? RowId::generate();
            $fields = $this->getFilterFields($filter);
            $values = $filter['values'];
            $values = $fields
                ->addValues($values)
                ->process()
                ->values()
                ->all();
            if (in_array($fields->get('values')->type(), $this->singleTypes)) {
                $values['values'] = [$values['values']];
            }
            $filter['values'] = $values;

            return $filter;
        })->all();
    }

    public function preProcess($data)
    {
        return collect($data)->map(function ($filter) {
            $filter['id'] = $filter['id'] ?? RowId::generate();
            $fields = $this->getFilterFields($filter);
            $values = $filter['values'];
            if (in_array($fields->get('values')->type(), $this->singleTypes)) {
                $values['values'] = $values['values'][0];
            }
            $values = $fields
                ->addValues($values)
                ->preProcess()
                ->values()
                ->all();
            $filter['values'] = $values;

            return $filter;
        })->all();
    }

    public function preProcessValidatable($data)
    {
        return collect($data)->map(function ($filter) {
            $fields = $this->getFilterFields($filter);
            $values = $filter['values'];
            $processed = $fields
                ->addValues($filter['values'])
                ->preProcessValidatables()
                ->values()
                ->all();
            $filter['values'] = array_merge($values, $processed);

            return $filter;
        })->all();
    }

    public function extraRules(): array
    {
        return collect($this->field->value())->map(function ($filter, $index) {
            $prefix = $this->field->handle().'.'.$index.'.values';
            $fields = $this->getFilterFields($filter);
            $values = $filter['values'];
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
        return collect($this->field->value())->map(function ($filter, $index) {
            $prefix = $this->field->handle().'.'.$index.'.values';
            $fields = $this->getFilterFields($filter);
            $values = $filter['values'];
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

        $existing = collect($this->field->value())->mapWithKeys(function ($filter) {
            return [$filter['id'] => $this->getFilterFields($filter)->addValues($filter['values'])->meta()];
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

        $collections = $this->config('mode', 'config') === 'config'
            ? $this->config('collections')
            : $this->field->parent()->get($this->config('field'));

        $groups = collect(Arr::wrap($collections))
            ->mapWithKeys(function ($collection) {
                $fields = Collection::findByHandle($collection)->entryBlueprints()
                    ->flatMap(function ($blueprint) {
                        return $blueprint
                            ->fields()
                            ->all()
                            ->filter->isFilterable();
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

    protected function getFilterFields($filter)
    {
        return $this->getFieldFields($this->getFields()[$filter['handle']]);
    }

    protected function getFieldFields(Field $field)
    {
        $fieldItems = match ($field->type()) {
            'toggle' => [
                'operator' => [
                    'type' => 'select',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.operator'),
                    'options' => [
                        '=' => __('Is'),
                        '<>' => __('Isn\'t'),
                    ],
                    'default' => '=',
                    'width' => 25,
                ],
                'values' => [
                    'display' => __('Value'),
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.value'),
                    'type' => 'toggle',
                    'inline_display' => __('False'),
                    'inline_label_when_true' => __('True'),
                    'width' => 50,
                ],
            ],
            'date' => [
                'operator' => [
                    'type' => 'select',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.operator'),
                    'options' => [
                        '<' => __('Before'),
                        '>' => __('After'),
                    ],
                    'default' => '<',
                    'width' => 25,
                ],
                'values' => [
                    'type' => 'date',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.value'),
                    'width' => 50,
                    'validate' => [
                        'required_without:{this}.variables',
                    ],
                ],
            ],
            'integer', 'float' => [
                'operator' => [
                    'type' => 'select',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.operator'),
                    'options' => [
                        '=' => __('Equals'),
                        '<>' => __('Not equals'),
                        '>' => __('Greater than'),
                        '>=' => __('Greater than or equals'),
                        '<' => __('Less than'),
                        '<=' => __('Less than or equals'),
                    ],
                    'default' => '=',
                    'width' => 25,
                ],
                'values' => [
                    'type' => 'list',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.values'),
                    'width' => 50,
                    'validate' => [
                        'required_without:{this}.variables',
                    ],
                ],
            ],
            'entries' => [
                'operator' => [
                    'type' => 'select',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.operator'),
                    'options' => [
                        '=' => __('Is'),
                        '<>' => __('Isn\'t'),
                    ],
                    'default' => '=',
                    'width' => 25,
                ],
                'values' => [
                    'type' => 'entries',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.values'),
                    'width' => 50,
                    'create' => false,
                    'collections' => $field->get('collections'),
                    'validate' => [
                        'required_without:{this}.variables',
                    ],
                ],
            ],
            'terms' => [
                'operator' => [
                    'type' => 'select',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.operator'),
                    'options' => [
                        '=' => __('Is'),
                        '<>' => __('Isn\'t'),
                    ],
                    'default' => '=',
                    'width' => 25,
                ],
                'values' => [
                    'type' => 'terms',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.values'),
                    'width' => 50,
                    'create' => false,
                    'taxonomies' => $field->get('taxonomies'),
                    'validate' => [
                        'required_without:{this}.variables',
                    ],
                ],
            ],
            'users' => [
                'operator' => [
                    'type' => 'select',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.operator'),
                    'options' => [
                        '=' => __('Is'),
                        '<>' => __('Isn\'t'),
                    ],
                    'default' => '=',
                    'width' => 25,
                ],
                'values' => [
                    'type' => 'users',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.values'),
                    'width' => 50,
                    'create' => false,
                    'validate' => [
                        'required_without:{this}.variables',
                    ],
                ],
            ],
            default => [
                'operator' => [
                    'type' => 'select',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.operator'),
                    'options' => [
                        '=' => __('Is'),
                        '<>' => __('Isn\'t'),
                        'like' => __('Contains'),
                    ],
                    'default' => '=',
                    'width' => 25,
                ],
                'values' => [
                    'type' => 'list',
                    'display' => __('statamic-filter-builder::fieldtypes.filter_builder.values'),
                    'width' => 50,
                    'validate' => [
                        'required_without:{this}.variables',
                    ],
                ],
            ],
        };

        $fieldItems['variables'] = [
            'type' => 'list',
            'display' => __('statamic-filter-builder::fieldtypes.filter_builder.variables'),
            'width' => 50,
            'validate' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    foreach ($value as $variable) {
                        if (! VariableParser::validate($variable)) {
                            $fail(__('statamic-filter-builder::validation.variables'));
                        }
                    }
                },
            ],
        ];

        $fields = collect($fieldItems)->map(function ($field, $handle) {
            return compact('handle', 'field');
        });

        return new Fields(
            $fields,
            $this->field()->parent(),
            $this->field()
        );
    }
}

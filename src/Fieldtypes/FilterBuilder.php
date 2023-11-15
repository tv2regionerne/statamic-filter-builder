<?php

namespace Tv2regionerne\StatamicFilterBuilder\Fieldtypes;

use Facades\Statamic\Fieldtypes\RowId;
use Statamic\Facades\Collection;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

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
                    'collections' => [
                        'display' => __('Collections'),
                        'instructions' => __('The filtered collections'),
                        'mode' => 'select',
                        'type' => 'collections',
                        'validate' => 'required_without:{this}.variables',
                    ],
                ],
            ],
        ];
    }

    public function process($data)
    {
        return collect($data)->map(function ($filter) {
            $filter['id'] = $filter['id'] ?? RowId::generate();
            $fields = $this->filterFields($filter);
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
            $fields = $this->filterFields($filter);
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
            $fields = $this->filterFields($filter);
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
            $fields = $this->filterFields($filter);
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
            $fields = $this->filterFields($filter);
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
        $fields = $this->fields()->map(function ($field) {
            return [
                'handle' => $field->handle(),
                'display' => $field->display(),
                'type' => $field->type(),
                'fields' => $this->fieldFields($field)->toPublishArray(),
            ];
        })->values();

        $existing = collect($this->field->value())->mapWithKeys(function ($filter) {
            return [$filter['id'] => $this->filterFields($filter)->addValues($filter['values'])->meta()];
        })->toArray();

        $defaults = $this->fields()->map(function ($field) {
            return $this->fieldFields($field)->all()->map(function ($field) {
                return $field->fieldtype()->preProcess($field->defaultValue());
            })->all();
        })->all();

        $new = $this->fields()->map(function ($field, $handle) use ($defaults) {
            return $this->fieldFields($field)->addValues($defaults[$handle])->meta();
        })->toArray();

        return [
            'fields' => $fields,
            'existing' => $existing,
            'new' => $new,
            'defaults' => $defaults,
        ];
    }

    protected function fields()
    {
        if ($this->fields) {
            return $this->fields;
        }

        $collections = $this->config('collections');

        return $this->fields = collect([
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

    protected function filterFields($filter)
    {
        return $this->fieldFields($this->fields()[$filter['handle']]);
    }

    protected function fieldFields(Field $field)
    {
        $fieldItems = match ($field->type()) {
            'toggle' => [
                'operator' => [
                    'type' => 'select',
                    'placeholder' => __('Select Operator'),
                    'options' => [
                        '=' => __('Is'),
                        '<>' => __('Isn\'t'),
                    ],
                    'default' => '=',
                    'width' => 25,
                ],
                'values' => [
                    'display' => __('Value'),
                    'type' => 'toggle',
                    'inline_label' => __('False'),
                    'inline_label_when_true' => __('True'),
                    'width' => 50,
                ],
            ],
            'date' => [
                'operator' => [
                    'type' => 'select',
                    'placeholder' => __('Select Operator'),
                    'options' => [
                        '<' => __('Before'),
                        '>' => __('After'),
                    ],
                    'default' => '<',
                    'width' => 25,
                ],
                'values' => [
                    'type' => 'date',
                    'width' => 50,
                    'validate' => [
                        'required_without:{this}.variables',
                    ],
                ],
            ],
            'integer', 'float' => [
                'operator' => [
                    'type' => 'select',
                    'placeholder' => __('Select Operator'),
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
                    'width' => 50,
                    'validate' => [
                        'required_without:{this}.variables',
                    ],
                ],
            ],
            'entries' => [
                'operator' => [
                    'type' => 'select',
                    'placeholder' => __('Select Operator'),
                    'options' => [
                        '=' => __('Is'),
                        '<>' => __('Isn\'t'),
                    ],
                    'default' => '=',
                    'width' => 25,
                ],
                'values' => [
                    'type' => 'entries',
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
                    'placeholder' => __('Select Operator'),
                    'options' => [
                        '=' => __('Is'),
                        '<>' => __('Isn\'t'),
                    ],
                    'default' => '=',
                    'width' => 25,
                ],
                'values' => [
                    'type' => 'terms',
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
                    'placeholder' => __('Select Operator'),
                    'options' => [
                        '=' => __('Is'),
                        '<>' => __('Isn\'t'),
                    ],
                    'default' => '=',
                    'width' => 25,
                ],
                'values' => [
                    'type' => 'users',
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
                    'placeholder' => __('Select Operator'),
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
                    'width' => 50,
                    'validate' => [
                        'required_without:{this}.variables',
                    ],
                ],
            ],
        };

        $fieldItems['variables'] = [
            'type' => 'list',
            'width' => 50,
            'validate' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    foreach ($value as $variable) {
                        if (! VariableParser::validate($variable)) {
                            $fail(__('This field contains invalid variables.'));
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

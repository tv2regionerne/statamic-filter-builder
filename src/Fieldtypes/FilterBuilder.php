<?php

namespace Tv2regionerne\StatamicFilterBuilder\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Tv2regionerne\StatamicFilterBuilder\VariableParser;

class FilterBuilder extends Fieldtype
{
    use Concerns\UsesFields;

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

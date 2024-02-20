<?php

namespace Tv2regionerne\StatamicFilterBuilder\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;

class SortBuilder extends Fieldtype
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
                        'instructions' => __('The sorted collections'),
                        'mode' => 'select',
                        'type' => 'collections',
                        'validate' => 'required_if:mode,config',
                        'if' => [
                            'mode' => 'config',
                        ],
                    ],
                    'field' => [
                        'display' => __('Field'),
                        'instructions' => __('The field listing the sorted collections'),
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

    public function augment($value)
    {
        if (! $value) {
            return;
        }

        return collect($value)
            ->map(fn ($sort) => $sort['handle'].':'.$sort['values']['direction'])
            ->join('|');
    }

    protected function getFieldFields(Field $field)
    {
        $fieldItems = [
            'direction' => [
                'type' => 'select',
                'display' => __('statamic-filter-builder::fieldtypes.sort_builder.direction'),
                'options' => [
                    'asc' => __('Ascending'),
                    'desc' => __('Descending'),
                ],
                'default' => 'asc',
                'width' => 25,
                'replicator_preview' => true,
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

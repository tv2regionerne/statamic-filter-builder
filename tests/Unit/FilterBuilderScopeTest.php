<?php

use Statamic\Facades;

uses(Tests\TestCase::class);

beforeEach(function () {
    app('statamic.scopes')->put('filter_builder', 'Tv2regionerne\\StatamicFilterBuilder\\Scopes\\FilterBuilder');

    $collection = tap(Facades\Collection::make()
        ->handle('pages'))
        ->save();

    $collection->entryBlueprints()->first()->setContents([
        'fields' => [
            [
                'handle' => 'title',
                'field' => [
                    'type' => 'text',
                ],
            ],
            [
                'handle' => 'entries',
                'field' => [
                    'type' => 'entries',
                    'max_items' => 5,
                ],
            ],
        ],
    ])->save();

    Facades\Entry::make()
        ->id('one')
        ->collection('pages')
        ->merge([
            'title' => 'One',
            'entries' => ['One'],
        ])
        ->save();

    Facades\Entry::make()
        ->id('two')
        ->collection('pages')
        ->merge([
            'title' => 'Two',
            'entries' => ['One', 'Two'],
        ])
        ->save();

    Facades\Entry::make()
        ->id('three')
        ->collection('pages')
        ->merge([
            'title' => 'Three',
            'entries' => ['One', 'Two', 'Three'],
        ])
        ->save();
});

it('filters text fields', function () {
    $result = (string) Facades\Antlers::parse('{{ collection:pages :filter_builder="params" }}{{ title }}{{ /collection:pages }}', [
        'params' => [
            [
                'handle' => 'title',
                'values' => [
                    'operator' => '=',
                    'values' => ['one'],
                    'variables' => [],
                ],
            ],
        ],
    ]);

    $this->assertSame('One', $result);

    $result = (string) Facades\Antlers::parse('{{ collection:pages :filter_builder="params" }}{{ title }}{{ /collection:pages }}', [
        'params' => [
            [
                'handle' => 'title',
                'values' => [
                    'operator' => '<>',
                    'values' => ['one'],
                    'variables' => [],
                ],
            ],
        ],
    ]);

    $this->assertSame('ThreeTwo', $result);

    $result = (string) Facades\Antlers::parse('{{ collection:pages :filter_builder="params" }}{{ title }}{{ /collection:pages }}', [
        'params' => [
            [
                'handle' => 'title',
                'values' => [
                    'operator' => '=',
                    'values' => ['one', 'two'],
                    'variables' => [],
                ],
            ],
        ],
    ]);

    $this->assertSame('OneTwo', $result);

    $result = (string) Facades\Antlers::parse('{{ collection:pages :filter_builder="params" }}{{ title }}{{ /collection:pages }}', [
        'params' => [
            [
                'handle' => 'title',
                'values' => [
                    'operator' => '=',
                    'values' => ['four'],
                    'variables' => [],
                ],
            ],
        ],
    ]);

    $this->assertSame('', $result);
});

it('filters fields with values from the cascade', function () {
    Facades\Cascade::set('cascade_variable', 'one');

    $result = (string) Facades\Antlers::parse('{{ collection:pages :filter_builder="params" }}{{ title }}{{ /collection:pages }}', [
        'params' => [
            [
                'handle' => 'title',
                'values' => [
                    'operator' => '=',
                    'values' => [],
                    'variables' => ['{{ cascade_variable }}'],
                ],
            ],
        ],
    ]);

    $this->assertSame('One', $result);
});

it('filters relationship fields', function () {
    $result = (string) Facades\Antlers::parse('{{ collection:pages :filter_builder="params" }}{{ title }}{{ /collection:pages }}', [
        'params' => [
            [
                'handle' => 'entries',
                'values' => [
                    'operator' => '=',
                    'values' => ['Three'],
                    'variables' => [],
                ],
            ],
        ],
    ]);

    //dd(Facades\Entry::query()->whereJsonContains('entries', ['One'])->get());

    $this->assertSame('Three', $result);

    $result = (string) Facades\Antlers::parse('{{ collection:pages :filter_builder="params" }}{{ title }}{{ /collection:pages }}', [
        'params' => [
            [
                'handle' => 'entries',
                'values' => [
                    'operator' => '<>',
                    'values' => ['Three'],
                    'variables' => [],
                ],
            ],
        ],
    ]);

    $this->assertSame('OneTwo', $result);

    $result = (string) Facades\Antlers::parse('{{ collection:pages :filter_builder="params" }}{{ title }}{{ /collection:pages }}', [
        'params' => [
            [
                'handle' => 'entries',
                'values' => [
                    'operator' => '=',
                    'values' => ['Three', 'Two'],
                    'variables' => [],
                ],
            ],
        ],
    ]);

    $this->assertSame('ThreeTwo', $result);

    $result = (string) Facades\Antlers::parse('{{ collection:pages :filter_builder="params" }}{{ title }}{{ /collection:pages }}', [
        'params' => [
            [
                'handle' => 'title',
                'values' => [
                    'operator' => '=',
                    'values' => ['Four'],
                    'variables' => [],
                ],
            ],
        ],
    ]);

    $this->assertSame('', $result);
});

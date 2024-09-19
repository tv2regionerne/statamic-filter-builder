<?php

use Statamic\Facades;
use Statamic\Tags\Collection\Collection as CollectionTag;

uses(Tests\TestCase::class);

beforeEach(function () {
    Facades\Collection::make()
        ->handle('pages')
        ->save();
});

it('adds query scope when none is applied', function () {
    $params = [];
    CollectionTag::hook('fetched-entries', function () use (&$params) {
        $params = $this->params->all();
    });

    Facades\Antlers::parse('{{ collection:pages filter_builder="something" }}{{ title }}{{ /collection:pages }}');

    $this->assertArrayHasKey('query_scope', $params);
});

it('doesnt add a query scope if one already exists', function () {
    $params = [];
    CollectionTag::hook('fetched-entries', function () use (&$params) {
        $params = $this->params->all();
    });

    Facades\Antlers::parse('{{ collection:pages filter_builder="something" query_scope="some_scope" }}{{ title }}{{ /collection:pages }}');

    $this->assertArrayHasKey('query_scope', $params);
    $this->assertNotSame('filter_builder', $params['query_scope']);
});

it('doesn\'t add query scope when no filter builder param is added', function () {
    $params = [];
    CollectionTag::hook('fetched-entries', function () use (&$params) {
        $params = $this->params->all();
    });

    Facades\Antlers::parse('{{ collection:pages }}{{ title }}{{ /collection:pages }}');

    $this->assertArrayNotHasKey('query_scope', $params);
});

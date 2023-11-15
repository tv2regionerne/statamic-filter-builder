<?php

use Carbon\Carbon;
use Tv2regionerne\StatamicFilterBuilder\VariableParser;

uses(Tests\TestCase::class);

it('parses string values', function () {
    $params = ['foo' => 'abcdefg'];
    $parsed = VariableParser::parse('{{ foo }}', $params);
    expect($parsed)
        ->toBeArray()
        ->toHaveCount(1)
        ->toMatchArray(['abcdefg']);
});

it('parses boolean values', function () {
    $params = ['foo' => true];
    $parsed = VariableParser::parse('{{ foo ? 1 : 0 }}', $params);
    expect($parsed)
        ->toBeArray()
        ->toHaveCount(1);
    expect($parsed[0])
        ->toBeTrue();

    $params = ['foo' => false];
    $parsed = VariableParser::parse('{{ foo ? 1 : 0 }}', $params);
    expect($parsed)
        ->toBeArray()
        ->toHaveCount(1);
    expect($parsed[0])
        ->toBeFalse();
});

it('parses date values', function () {
    $now = Carbon::now();
    $params = ['foo' => $now];
    $parsed = VariableParser::parse('{{ foo }}', $params);
    expect($parsed)
        ->toBeArray()
        ->toHaveCount(1);
    expect($parsed[0])
        ->toBeInstanceOf(Carbon::class);
    expect($parsed[0]->toDateTimeString())
        ->toEqual($now->toDateTimeString());
});

it('parses multiple values', function () {
    $params = ['foo' => ['abcdefg', '1234567']];
    $parsed = VariableParser::parse('{{ foo | join }}', $params);
    expect($parsed)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray(['abcdefg', '1234567']);

    $params = [
        'foo' => [
            ['id' => 'abcdefg'],
            ['id' => '1234567'],
        ],
    ];
    $parsed = VariableParser::parse('{{ foo | pluck("id") | join }}', $params);
    expect($parsed)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray(['abcdefg', '1234567']);
});

it('parses values as json', function () {
    $params = ['foo' => true];
    $parsed = VariableParser::parse('{{ foo | to_json }}', $params);
    expect($parsed)
        ->toBeArray()
        ->toHaveCount(1);
    expect($parsed[0])
        ->toBeTrue();

    $params = [
        'foo' => [
            ['id' => 'abcdefg'],
            ['id' => '1234567'],
        ],
    ];
    $parsed = VariableParser::parse('{{ foo | pluck("id") | to_json }}', $params);
    expect($parsed)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray(['abcdefg', '1234567']);
});

it('skips invalid values', function () {
    $parsed = VariableParser::parse('abcdefg');
    expect($parsed)
        ->toBeNull();

    $parsed = VariableParser::parse('{{ foo }}');
    expect($parsed)
        ->toBeNull();

    $parsed = VariableParser::parse('{{ if }}');
    expect($parsed)
        ->toBeNull();

    $parsed = VariableParser::parse('{{ my_variable');
    expect($parsed)
        ->toBeNull();

    $parsed = VariableParser::parse('{{ test }} abcdefg');
    expect($parsed)
        ->toBeNull();
});

it('validates values', function () {
    $parsed = VariableParser::validate('{{ foo }}');
    expect($parsed)
        ->toBeTrue();

    $parsed = VariableParser::validate('{{ if }}');
    expect($parsed)
        ->toBeFalse();

    $parsed = VariableParser::validate('abcdefg');
    expect($parsed)
        ->toBeFalse();
});

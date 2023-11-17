<?php

namespace Tv2regionerne\StatamicFilterBuilder;

use Carbon\Carbon;
use Statamic\Facades\Antlers;
use Statamic\Support\Arr;

class VariableParser
{
    public static function parse($variable, array $params = [])
    {
        if (! preg_match('/^\{\{.*}}$/', $variable)) {
            return;
        }

        try {
            $parsed = (string) Antlers::parse($variable, $params);
            if ($parsed === '') {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        $decoded = json_decode($parsed, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decoded = preg_split('/\s*,\s*/', $parsed);
        }

        // array is multidimensional. Don't return it to the query
        if (is_array($decoded) && count($decoded)!==count($decoded,COUNT_RECURSIVE)) {
            return;
        }

        return Arr::map(Arr::wrap($decoded), function ($value) {
            return self::castValue($value);
        });
    }

    public static function validate($variable)
    {
        if (! preg_match('/^\{\{.*}}$/', $variable)) {
            return false;
        }

        try {
            Antlers::parse($variable);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    protected static function castValue($value)
    {
        if ($value === 1 || $value === '1') {
            return true;
        }

        if ($value === 0 || $value === '0') {
            return false;
        }

        if (is_string($value) && Carbon::canBeCreatedFromFormat($value, 'Y-m-d H:i:s')) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $value);
        }

        return $value;
    }
}

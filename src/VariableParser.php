<?php

namespace Tv2regionerne\StatamicFilterBuilder;

use Statamic\Facades\Antlers;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class VariableParser
{
    public static function parse($variable, array $params = [])
    {
        if (! preg_match('/^\{\{.*\}\}$/', $variable)) {
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

        if (Str::contains($variable, 'to_json')) {
            $parsed = Arr::wrap(json_decode($parsed, true));
        } else {
            $parsed = Arr::map(preg_split('/\s*,\s*/', $parsed), fn ($value) => self::castValue($value));
        }

        return $parsed;
    }

    public static function validate($variable)
    {
        if (! preg_match('/^\{\{.*\}\}$/', $variable)) {
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
        if ($value === '1') {
            return true;
        } elseif ($value === '0') {
            return false;
        }

        return $value;
    }
}

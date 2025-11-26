<?php

namespace AppMaker\Utils;

use Illuminate\Support\Str;

class StrHelpers
{
    public static function functionName(mixed $value): string
    {
        if (is_array($value)) {
            $value = implode(' ', array_values(array_filter($value, fn ($v) => is_numeric($v) || is_string($v))));
        }

        return is_numeric($value) || is_string($value) ? Str::slug($value) : '';
    }
}

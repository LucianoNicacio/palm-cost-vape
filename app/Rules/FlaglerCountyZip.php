<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FlaglerCountyZip implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowed = config('geo.allowed_zips', []);

        // Normalize: take only the first 5 digits (handle ZIP+4 like 32137-1234)
        $zip = substr(preg_replace('/[^0-9]/', '', $value), 0, 5);

        if (!in_array($zip, $allowed, true)) {
            $fail('We currently only serve Flagler County residents.');
        }
    }
}

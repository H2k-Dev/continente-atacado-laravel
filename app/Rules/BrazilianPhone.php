<?php

namespace App\Rules;

use App\Support\PhoneMask;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BrazilianPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! PhoneMask::isValid(is_string($value) ? $value : null)) {
            $fail('Informe um telefone válido com DDD.');
        }
    }
}
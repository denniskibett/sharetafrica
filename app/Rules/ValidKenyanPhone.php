<?php

namespace App\Rules;

use App\Helpers\PhoneHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidKenyanPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // nullability handled by the caller
        }

        if (!PhoneHelper::format($value)) {
            $fail('Please enter a valid Kenyan phone number (e.g., 0712345678).');
        }
    }
}
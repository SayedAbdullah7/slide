<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaudiIban implements ValidationRule
{
    /**
     * Run the validation rule.
     * Validates Saudi Arabia IBAN format (24 characters, starts with SA)
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove spaces for validation
        $iban = str_replace(' ', '', strtoupper(trim($value)));

        // Saudi IBAN must be 24 characters and start with SA
        if (strlen($iban) !== 24) {
            $fail('The :attribute must be 24 characters long.');
            return;
        }

        if (!str_starts_with($iban, 'SA')) {
            $fail('The :attribute must start with SA for Saudi Arabia.');
            return;
        }

        // IBAN format: SA + 2 check digits + 2 bank code + 18 account number
        // Validate format: SA + 22 alphanumeric characters
        if (!preg_match('/^SA[0-9]{22}$/', $iban)) {
            $fail('The :attribute format is invalid. Expected format: SA followed by 22 digits.');
            return;
        }
    }
}

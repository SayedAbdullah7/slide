<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaudiPhoneNumber implements ValidationRule
{
    /**
     * Allowed format
     *
     * Options:
     * - 'all': All formats (default)
     * - 'local': Only 05XXXXXXXX or 5XXXXXXXX
     * - 'international': Only 009665XXXXXXXX, +9665XXXXXXXX, or 9665XXXXXXXX
     * - 'international_plus': Only +9665XXXXXXXX (must start with +)
     */
    protected string $format;

    /**
     * Create a new rule instance.
     *
     * @param string $format Format to allow: 'all', 'local', 'international', or 'international_plus'
     */
    public function __construct(string $format = 'all')
    {
        $this->format = $format;
    }

    /**
     * Run the validation rule.
     *
     * Validates Saudi Arabia mobile phone numbers.
     *
     * Accepts formats:
     * - 009665XXXXXXXX (international with 00)
     * - 9665XXXXXXXX (international without +)
     * - +9665XXXXXXXX (international with +)
     * - 05XXXXXXXX (local format)
     * - 5XXXXXXXX (local without 0)
     *
     * Validates telecom company prefixes:
     * - 0, 5, 3: STC
     * - 6, 4: Mobily
     * - 9, 8: Zain
     * - 7: MVNO (Virgin and Lebara)
     * - 1: Bravo
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Define patterns based on format
        $patterns = [
            'all' => '/^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/',
            'local' => '/^(05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/',
            'international' => '/^(009665|9665|\+9665)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/',
            'international_plus' => '/^\+9665(5|0|3|6|4|9|1|8|7)([0-9]{7})$/',
        ];

        $pattern = $patterns[$this->format] ?? $patterns['international_plus'];

        if (!preg_match($pattern, $value)) {
            $fail('رقم الجوال يجب أن يكون رقم سعودي صحيح');
        }
    }
}

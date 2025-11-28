<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RestrictedDomain implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get restricted domains from config
        $restricted = config('app.restricted_domains');

        // Convert env string → array
        $restrictedDomains = $restricted ? explode(',', $restricted) : [];

        // Get domain from email
        $emailDomain = strtolower(substr(strrchr($value, "@"), 1)); 

        if (in_array($emailDomain, $restrictedDomains)) {
            $fail("The {$attribute} domain '{$emailDomain}' is not allowed.");
        }
    }
}

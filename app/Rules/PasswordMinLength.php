<?php

namespace App\Rules;

use App\Services\SettingsService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordMinLength implements ValidationRule
{
    protected SettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = app(SettingsService::class);
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $minLength = (int) $this->settingsService->get('password_min_length', '8');

        if (strlen($value) < $minLength) {
            $fail("The {$attribute} must be at least {$minLength} characters.");
        }
    }
}


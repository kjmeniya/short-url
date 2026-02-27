<?php

namespace App\Rules;

use App\Models\ShortUrl;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that a slug (custom_alias):
 *  - Is not a reserved system keyword.
 *  - Is not already taken by another record.
 *
 * Usage in FormRequests:
 *   new SlugAvailable($excludeId)   — pass current record ID on update to ignore self.
 */
class SlugAvailable implements ValidationRule
{
    /**
     * Reserved slugs that must never be used by users.
     */
    public const RESERVED = [
        'admin', 'login', 'register', 'api', 'dashboard',
        'pricing', 'terms', 'privacy', 'assets',
        'logout', 'home', 'about', 'contact', 'user',
        'shorten', 'redirect', 'health', 'css', 'js',
    ];

    public function __construct(
        private readonly ?int $excludeId = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slug = strtolower((string) $value);

        // 1. Block reserved slugs
        if (in_array($slug, self::RESERVED, true)) {
            $fail("The slug \"{$slug}\" is reserved and cannot be used.");
            return;
        }

        // 2. Uniqueness check (exclude current record on update)
        $query = ShortUrl::withTrashed()->where('custom_alias', $slug);

        if ($this->excludeId !== null) {
            $query->where('id', '!=', $this->excludeId);
        }

        if ($query->exists()) {
            $fail('This slug is already taken. Please choose a different one.');
        }
    }
}

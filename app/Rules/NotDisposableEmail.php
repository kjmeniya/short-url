<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotDisposableEmail implements ValidationRule
{
    /**
     * Local fallback blocklist
     */
    protected array $localBlocklist = [
        '10minutemail.com',
        '10minutemail.net',
        'guerrillamail.com',
        'mailinator.com',
        'temp-mail.org',
        'tempmail.com',
        'yopmail.com',
        'getnada.com',
        'mohmal.com',
        'mailsac.com',
        'dropmail.me',
        'trashmail.com',
        'tempmail.dev',
    ];

    /**
     * Validate email
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !str_contains($value, '@')) {
            return;
        }

        $domain = strtolower(substr(strrchr($value, '@'), 1));

        /** STEP 1: Local fallback blocklist */
        if (in_array($domain, $this->localBlocklist, true)) {
            $fail('Disposable or temporary email addresses are not allowed.');
            return;
        }

        /** STEP 2: GitHub Global Disposable List (150k domains) */
        if ($this->isInGlobalList($domain)) {
            $fail('Disposable or temporary email addresses are not allowed.');
            return;
        }

        /** STEP 3: MX Records (invalid emails) */
        if (!$this->hasValidMx($domain)) {
            $fail('This email domain is invalid or cannot accept mail.');
            return;
        }

        /** STEP 4: External API checks */
        if ($this->isDisposableViaApi($value, $domain)) {
            $this->storeDiscoveredDomain($domain);
            $fail('Disposable or temporary email addresses are not allowed.');
            return;
        }
    }

    /**
     * Check MX / A records
     */
    protected function hasValidMx(string $domain): bool
    {
        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    }

    /**
     * GitHub Disposable Domain List (best global detection)
     */
    protected function isInGlobalList(string $domain): bool
    {
        $list = $this->getGlobalDisposableList();
        return in_array($domain, $list, true);
    }

    /**
     * Download & cache GitHub’s master disposable domain list (24 hours)
     */
    protected function getGlobalDisposableList(): array
    {
        return Cache::remember('global_disposable_domains', now()->addDay(), function () {
            try {
                $url = 'https://cdn.jsdelivr.net/npm/disposable-email-domains/index.json';

                $response = Http::timeout(6)->get($url);

                if ($response->successful()) {
                    $json = $response->json();
                    return $json['disposable'] ?? [];
                }
            } catch (\Exception $e) {
                Log::warning("Disposable list fetch failed", [
                    'error' => $e->getMessage(),
                ]);
            }

            return [];
        });
    }

    /**
     * Check via public APIs
     */
    protected function isDisposableViaApi(string $email, string $domain): bool
    {
        $cacheKey = "disposable-check:$domain";

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($email, $domain) {

            /** API 1: Debounce (email-based) */
            try {
                $response = Http::timeout(3)->get(
                    'https://disposable.debounce.io',
                    ['email' => $email]
                );

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['disposable'] ?? 'false') === 'true') {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Debounce failed", [
                    'domain' => $domain,
                    'error' => $e->getMessage()
                ]);
            }

            /** API 2: Kickbox (domain-based) */
            try {
                $response = Http::timeout(3)->get(
                    "https://open.kickbox.com/v1/disposable/$domain"
                );

                if ($response->successful()) {
                    if (($response->json()['disposable'] ?? false) === true) {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Kickbox failed", [
                    'domain' => $domain,
                    'error' => $e->getMessage()
                ]);
            }

            return false;
        });
    }

    /**
     * Store newly discovered domains in cache
     */
    protected function storeDiscoveredDomain(string $domain): void
    {
        try {
            $cacheKey = 'custom_disposable_domains';
            $list = Cache::get($cacheKey, []);

            if (!in_array($domain, $list)) {
                $list[] = $domain;
                Cache::put($cacheKey, $list, now()->addDays(30));

                Log::info("New disposable domain detected", ['domain' => $domain]);
            }
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}

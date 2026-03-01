<?php

namespace App\Traits;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Trait HasPlanFeatures
 *
 * Attach to any controller that needs to know which plan features
 * the currently authenticated user is allowed to use.
 *
 * Usage in a controller method:
 *   $features = $this->getUserPlanFeatures();
 *   return view('...', compact('features'));
 *
 * In the view:
 *   @if($features->get('custom_slug'))  ... @endif
 */
trait HasPlanFeatures
{
    /**
     * Return a keyed Collection:  feature_name => is_include (bool)
     * Admins / Super-admins always get all features enabled.
     *
     * @param  int|null  $userId  Defaults to the authenticated user
     * @return \Illuminate\Support\Collection<string, bool>
     */
    protected function getUserPlanFeatures(?int $userId = null): Collection
    {
        $user = $userId ? \App\Models\User::find($userId) : Auth::user();

        // Admins have every feature
        if ($user && ($user->isAdmin() || $user->isSuperAdmin())) {
            return $this->allFeaturesEnabled();
        }

        // Find the user's active subscription with its plan features
        $subscription = Subscription::where('user_id', $user?->id ?? 0)
            ->where('status', 'active')
            ->latest()
            ->with(['plan.features'])
            ->first();

        if (!$subscription || !$subscription->plan) {
            // No active plan → only basic create_link
            return $this->defaultFreeFeatures();
        }

        return $subscription->plan->features
            ->keyBy('feature_name')
            ->map(fn($f) => (bool) $f->is_include);
    }

    /**
     * A Collection of all possible feature names mapped to TRUE.
     * Used for admins who bypass plan restrictions.
     */
    private function allFeaturesEnabled(): Collection
    {
        return collect([
            'create_link'              => true,
            'custom_slug'              => true,
            'expiry_date'              => true,
            'click_limit'              => true,
            'password_protection'      => true,
            '24h_story_link'           => true,
            'one_time_link'            => true,
            'private_link'             => true,
            'device_redirect'          => true,
            'office_hours_redirect'    => true,
            'custom_og_preview'        => true,
            'ip_blocking'              => true,
            'redirect_delay'           => true,
            'analytics'                => true,
            'api_access'               => true,
        ]);
    }

    /**
     * The absolute minimum set (matches Free plan seeder).
     */
    private function defaultFreeFeatures(): Collection
    {
        return collect([
            'create_link'              => true,
            'custom_slug'              => false,
            'expiry_date'              => false,
            'click_limit'              => false,
            'password_protection'      => false,
            '24h_story_link'           => false,
            'one_time_link'            => false,
            'private_link'             => false,
            'device_redirect'          => false,
            'office_hours_redirect'    => false,
            'custom_og_preview'        => false,
            'ip_blocking'              => false,
            'redirect_delay'           => false,
            'analytics'                => false,
            'api_access'               => false,
        ]);
    }
}

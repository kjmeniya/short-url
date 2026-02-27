<?php

namespace App\Services;

use App\Models\ShortUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * ShortUrlService
 *
 * Central business-logic layer for ShortUrl CRUD.
 * Both the User panel and the Admin panel controllers delegate here,
 * keeping the controllers thin and the logic in one place.
 *
 * Ownership scoping:
 *   - Pass an integer $ownerId to restrict operations to records owned by
 *     that user (user panel).
 *   - Pass null to allow access to every record (admin panel).
 */
class ShortUrlService
{
    // ── Listing ───────────────────────────────────────────────────────────────

    /**
     * Return a paginated list of short URLs, optionally scoped to an owner.
     *
     * @param  string|null  $search    Free-text search term.
     * @param  string|null  $status    Filter by status (active|inactive|expired).
     * @param  int|null     $ownerId   If set, only returns records created by this user.
     * @param  int          $perPage
     */
    public function paginate(
        ?string $search,
        ?string $status,
        ?int $ownerId = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = ShortUrl::orderBy('created_at', 'desc');

        if ($ownerId !== null) {
            $query->where('created_by', $ownerId);
        }

        if ($search) {
            $query->search($search);
        }

        if ($status && in_array($status, ['active', 'inactive', 'expired'])) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    // ── Find (with optional owner-scope) ──────────────────────────────────────

    /**
     * Find a short URL, optionally enforcing ownership.
     * Throws ModelNotFoundException on failure (triggers 404).
     *
     * @param  int       $id
     * @param  int|null  $ownerId  When set, also requires created_by = $ownerId.
     */
    public function findOrFail(int $id, ?int $ownerId = null): ShortUrl
    {
        $query = ShortUrl::where('id', $id);

        if ($ownerId !== null) {
            $query->where('created_by', $ownerId);
        }

        return $query->firstOrFail();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    /**
     * Create a new short URL.
     *
     * @param  array     $data      Validated fields: original_url, title?,
     *                              custom_alias?, expires_at?, status?,
     *                              password? (plain-text, will be hashed here).
     * @param  int       $actorId   The user performing the action (created_by / updated_by).
     */
    public function create(array $data, int $actorId): ShortUrl
    {
        $is24h = !empty($data['is_24h_story']);
        $expiresAt = $data['expires_at'] ?? null;
        if ($is24h) {
            $expiresAt = now()->addHours(24);
        }

        $shortUrl = ShortUrl::create([
            'code'         => \App\Models\ShortUrl::generateUniqueCode(),
            'original_url' => $data['original_url'],
            'mobile_url'   => $data['mobile_url']   ?? null,
            'desktop_url'  => $data['desktop_url']  ?? null,
            'tablet_url'   => $data['tablet_url']   ?? null,
            'title'        => $data['title']        ?? null,
            'custom_alias' => $data['custom_alias'] ?? null ?: null,
            'timezone'     => $data['timezone']     ?? null,
            'office_days'  => $data['office_days']  ?? null,
            'office_start_time' => $data['office_start_time'] ?? null,
            'office_end_time'   => $data['office_end_time'] ?? null,
            'office_url'      => $data['office_url'] ?? null,
            'after_hours_url' => $data['after_hours_url'] ?? null,
            'og_title'        => $data['og_title'] ?? null,
            'og_description'  => $data['og_description'] ?? null,
            'og_image'        => $data['og_image'] ?? null,
            'redirect_delay'  => $data['redirect_delay'] ?? 0,
            'expires_at'   => $expiresAt,
            'max_clicks'   => $data['max_clicks']   ?? null ?: null,
            'status'       => $data['status']       ?? 'active',
            'is_private'   => !empty($data['is_private']),
            'is_24h_story' => $is24h,
            'is_one_time'  => !empty($data['is_one_time']),
            'password'     => isset($data['password']) && $data['password']
                                ? bcrypt($data['password'])
                                : null,
            'created_by'   => $actorId,
            'updated_by'   => $actorId,
        ]);

        if (!empty($data['ip_blocks']) && is_array($data['ip_blocks'])) {
            foreach ($data['ip_blocks'] as $ipBlock) {
                if (!empty($ipBlock['value']) && !empty($ipBlock['type'])) {
                    $shortUrl->ipBlocks()->create([
                        'type'  => $ipBlock['type'],
                        'value' => $ipBlock['value'],
                    ]);
                }
            }
        }

        return $shortUrl;
    }

    // ── Update ────────────────────────────────────────────────────────────────

    /**
     * Update an existing short URL.
     *
     * @param  ShortUrl  $shortUrl  The model to update.
     * @param  array     $data      Validated fields (same set as create, minus password logic).
     * @param  int       $actorId   The user performing the action (updated_by).
     */
    public function update(ShortUrl $shortUrl, array $data, int $actorId): ShortUrl
    {
        $is24h = array_key_exists('is_24h_story', $data) ? !empty($data['is_24h_story']) : $shortUrl->is_24h_story;
        $expiresAt = $data['expires_at'] ?? null ?: null;

        if ($is24h && !$shortUrl->is_24h_story) {
            $expiresAt = now()->addHours(24);
        } elseif ($is24h && $shortUrl->is_24h_story) {
            $expiresAt = $shortUrl->expires_at;
        }

        $payload = [
            'original_url' => $data['original_url'],
            'mobile_url'   => $data['mobile_url']   ?? null,
            'desktop_url'  => $data['desktop_url']  ?? null,
            'tablet_url'   => $data['tablet_url']   ?? null,
            'title'        => $data['title']        ?? null,
            'custom_alias' => $data['custom_alias'] ?? null ?: null,
            'timezone'     => $data['timezone']     ?? null,
            'office_days'  => $data['office_days']  ?? null,
            'office_start_time' => $data['office_start_time'] ?? null,
            'office_end_time'   => $data['office_end_time'] ?? null,
            'office_url'      => $data['office_url'] ?? null,
            'after_hours_url' => $data['after_hours_url'] ?? null,
            'og_title'        => $data['og_title'] ?? null,
            'og_description'  => $data['og_description'] ?? null,
            'og_image'        => $data['og_image'] ?? null,
            'redirect_delay'  => array_key_exists('redirect_delay', $data) ? (int)$data['redirect_delay'] : $shortUrl->redirect_delay,
            'expires_at'   => $expiresAt,
            'max_clicks'   => isset($data['max_clicks']) && $data['max_clicks'] ? (int) $data['max_clicks'] : null,
            'status'       => $data['status']        ?? $shortUrl->status,
            'is_private'   => array_key_exists('is_private', $data) ? !empty($data['is_private']) : $shortUrl->is_private,
            'is_24h_story' => $is24h,
            'is_one_time'  => array_key_exists('is_one_time', $data) ? !empty($data['is_one_time']) : $shortUrl->is_one_time,
            'updated_by'   => $actorId,
        ];

        // Only re-hash when a new password is provided; leave existing otherwise.
        if (!empty($data['password'])) {
            $payload['password'] = bcrypt($data['password']);
        }
        // Admin or user explicitly requested to clear the password
        elseif (!empty($data['clear_password'])) {
            $payload['password'] = null;
        }

        $shortUrl->update($payload);

        if (array_key_exists('ip_blocks', $data)) {
            if (is_array($data['ip_blocks'])) {
                $keepIds = collect($data['ip_blocks'])->pluck('id')->filter()->toArray();
                $shortUrl->ipBlocks()->whereNotIn('id', $keepIds)->delete();

                foreach ($data['ip_blocks'] as $ipBlock) {
                    if (!empty($ipBlock['value']) && !empty($ipBlock['type'])) {
                        if (!empty($ipBlock['id'])) {
                            $shortUrl->ipBlocks()->where('id', $ipBlock['id'])->update([
                                'type'  => $ipBlock['type'],
                                'value' => $ipBlock['value'],
                            ]);
                        } else {
                            $shortUrl->ipBlocks()->create([
                                'type'  => $ipBlock['type'],
                                'value' => $ipBlock['value'],
                            ]);
                        }
                    }
                }
            } else {
                $shortUrl->ipBlocks()->delete();
            }
        }

        return $shortUrl->fresh();
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    /**
     * Soft-delete a short URL.
     *
     * @param  ShortUrl  $shortUrl
     */
    public function delete(ShortUrl $shortUrl): void
    {
        $shortUrl->delete();
    }

    // ── Toggle Status ─────────────────────────────────────────────────────────

    /**
     * Toggle between active ↔ inactive.
     *
     * @param  ShortUrl  $shortUrl
     * @param  int       $actorId
     */
    public function toggleStatus(ShortUrl $shortUrl, int $actorId): ShortUrl
    {
        $shortUrl->update([
            'status'     => $shortUrl->status === 'active' ? 'inactive' : 'active',
            'updated_by' => $actorId,
        ]);

        return $shortUrl->fresh();
    }

    // ── Stats helper ──────────────────────────────────────────────────────────

    /**
     * Return click/link counts, optionally scoped to an owner.
     *
     * @param  int|null  $ownerId  If set, counts only that user's links.
     */
    public function getStats(?int $ownerId = null): array
    {
        $query = ShortUrl::query();

        if ($ownerId !== null) {
            $query->where('created_by', $ownerId);
        }

        return [
            'total'        => (clone $query)->count(),
            'active'       => (clone $query)->where('status', 'active')->count(),
            'inactive'     => (clone $query)->where('status', 'inactive')->count(),
            'expired'      => (clone $query)->where('status', 'expired')->count(),
            'total_clicks' => (clone $query)->sum('clicks'),
            'this_month'   => (clone $query)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    // ── Slug Availability Check ───────────────────────────────────────────────

    /**
     * Check whether a slug is available and valid.
     * Used by the AJAX inline check on create/edit forms.
     *
     * @param  string    $slug       Raw input from the user.
     * @param  int|null  $excludeId  Current record ID to ignore (update flow).
     */
    public function checkSlugAvailability(string $slug, ?int $excludeId = null): \Illuminate\Http\JsonResponse
    {
        $slug = strtolower(trim($slug));

        // Empty → treat as "will auto-generate"
        if ($slug === '') {
            return response()->json([
                'available' => true,
                'status'    => 'empty',
                'message'   => 'A short code will be auto-generated.',
            ]);
        }

        // Length
        if (strlen($slug) < 3) {
            return response()->json([
                'available' => false,
                'status'    => 'too_short',
                'message'   => 'Slug must be at least 3 characters.',
            ]);
        }

        if (strlen($slug) > 64) {
            return response()->json([
                'available' => false,
                'status'    => 'too_long',
                'message'   => 'Slug may not exceed 64 characters.',
            ]);
        }

        // Format
        if (!preg_match('/^[a-z0-9\-_]+$/', $slug)) {
            return response()->json([
                'available' => false,
                'status'    => 'invalid_format',
                'message'   => 'Only lowercase letters, numbers, hyphens, and underscores are allowed.',
            ]);
        }

        // Reserved
        $reserved = \App\Rules\SlugAvailable::RESERVED;
        if (in_array($slug, $reserved, true)) {
            return response()->json([
                'available' => false,
                'status'    => 'reserved',
                'message'   => "'{$slug}' is a reserved keyword and cannot be used.",
            ]);
        }

        // Uniqueness
        $query = ShortUrl::withTrashed()->where('custom_alias', $slug);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            return response()->json([
                'available' => false,
                'status'    => 'taken',
                'message'   => 'This slug is already taken. Please choose another.',
            ]);
        }

        return response()->json([
            'available' => true,
            'status'    => 'available',
            'message'   => 'This slug is available!',
            'slug'      => $slug,
        ]);
    }
}

<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\PageVisit;
use Illuminate\Support\Facades\Cache;

class ProcessPageVisit implements ShouldQueue
{
    use Queueable;

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userId = $this->data['user_id'] ?? null;
        $guestId = $this->data['guest_id'] ?? 'unknown';
        $page = $this->data['page'] ?? '/';

        // Identify the user uniquely for the cache key
        $identifier = $userId ? "u_{$userId}" : "g_{$guestId}";
        $pageHash = md5($page);
        $cacheKey = "page_visit:{$identifier}:{$pageHash}";

        // Enforce uniqueness
        $cacheTime = analytics_deduplication_time();
        if (Cache::has($cacheKey)) {
            return; // Duplicate visit within cache time
        }

        // Set cache lock
        Cache::put($cacheKey, true, $cacheTime);

        // Store the visit
        PageVisit::create([
            'user_id' => $userId,
            'guest_id' => $userId ? null : $guestId, // Store guest_id only if not authenticated
            'page' => $page,
            'platform' => $this->data['platform'] ?? 'web',
            'device' => $this->data['device'] ?? 'desktop',
            'ip' => $this->data['ip'] ?? null,
            'visited_at' => $this->data['visited_at'] ?? now(),
        ]);
    }
}

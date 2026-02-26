<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use App\Services\UserAgentParser;

class ShortUrlClick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'short_url_id',
        'ip_address',
        'browser',
        'browser_version',
        'os',
        'os_version',
        'device_type',
        'device_name',
        'user_agent',
        'country',
        'country_code',
        'city',
        'referrer',
        'referrer_domain',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function shortUrl()
    {
        return $this->belongsTo(ShortUrl::class);
    }

    // ── Factory helper ────────────────────────────────────────────────────────

    /**
     * Build a click record array from the current HTTP request.
     * Geo data is fetched from the configured ip_api_url setting.
     */
    public static function fromRequest(\Illuminate\Http\Request $request, int $shortUrlId): array
    {
        $ua     = $request->userAgent() ?? '';
        $parser = new UserAgentParser($ua);

        // Browser name + version
        $rawBrowser = $parser->browser(); // e.g. "Chrome 120.0"
        [$browserName, $browserVer] = static::splitBrowserVersion($rawBrowser);

        // Device type
        $deviceType = 'desktop';
        if ($parser->isBot())        $deviceType = 'bot';
        elseif ($parser->isTablet()) $deviceType = 'tablet';
        elseif ($parser->isMobile()) $deviceType = 'mobile';

        // Referrer
        $referrer       = $request->header('referer', '');
        $referrerDomain = '';
        if ($referrer) {
            $host = parse_url($referrer, PHP_URL_HOST);
            $referrerDomain = $host ? preg_replace('/^www\./i', '', $host) : '';
        }

        $ip  = $request->ip();
        $geo = static::lookupGeo($ip);

        return [
            'short_url_id'    => $shortUrlId,
            'ip_address'      => $ip,
            'browser'         => $browserName,
            'browser_version' => $browserVer,
            'os'              => $parser->platform(),
            'os_version'      => '',
            'device_type'     => $deviceType,
            'device_name'     => '',
            'user_agent'      => substr($ua, 0, 512),
            'country'         => $geo['country'] ?? null,
            'country_code'    => $geo['country_code'] ?? null,
            'city'            => $geo['city'] ?? null,
            'referrer'        => $referrer ? substr($referrer, 0, 500) : null,
            'referrer_domain' => $referrerDomain ?: null,
            'clicked_at'      => now(),
        ];
    }

    /**
     * Look up country / city from IP using the configured ip_api_url setting.
     * Returns empty array on any failure (never throws).
     */
    protected static function lookupGeo(string $ip): array
    {
        // Skip local / private IPs
        if (in_array($ip, ['127.0.0.1', '::1']) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return ['country' => 'Local', 'city' => 'Local', 'country_code' => 'LC'];
        }

        try {
            $baseUrl = function_exists('ip_api_url') ? ip_api_url() : 'https://ip-api.in/api/v1/ip/';
            $url     = rtrim($baseUrl, '/') . '/' . $ip;

            $http = \Illuminate\Support\Facades\Http::timeout(3);

            // Attach bearer token if available
            if (function_exists('ip_api_token')) {
                $token = ip_api_token();
                if ($token) {
                    $http = $http->withToken($token);
                }
            }

            $response = $http->get($url);

            if ($response->successful()) {
                $data = $response->json();

                // ip-api.in response: { success: true, data: { country, city, country_code, ... } }
                if (!empty($data['success']) && !empty($data['data'])) {
                    $geo = $data['data'];
                    return [
                        'country'      => $geo['country']      ?? null,
                        'country_code' => $geo['country_code'] ?? null,
                        'city'         => $geo['city']         ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('ShortUrl GeoIP lookup failed: ' . $e->getMessage());
        }

        return [];
    }


    // ── Analytics helpers ─────────────────────────────────────────────────────

    public static function clicksOverTime(int $shortUrlId, int $days = 30): array
    {
        $rows = static::where('short_url_id', $shortUrlId)
            ->where('clicked_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $labels = [];
        $data   = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d        = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $data[]   = $rows[$d] ?? 0;
        }

        return compact('labels', 'data');
    }

    public static function topBy(int $shortUrlId, string $field, int $limit = 8): array
    {
        return static::where('short_url_id', $shortUrlId)
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->selectRaw("{$field} as label, COUNT(*) as total")
            ->groupBy($field)
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn($r) => ['label' => $r->label, 'total' => (int)$r->total])
            ->toArray();
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private static function splitBrowserVersion(string $raw): array
    {
        // existing parser returns "Chrome 120.0.0.0" style
        if (preg_match('/^(.+?)\s+([\d.]+)$/', $raw, $m)) {
            return [trim($m[1]), $m[2]];
        }
        return [$raw, ''];
    }
}

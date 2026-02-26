<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ContactRequest;
use App\Models\Contact;
use App\Models\ShortUrl;
use App\Models\ShortUrlClick;
use App\Services\EmailService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HomeController extends Controller
{
    /** Cookie name & lifetime (365 days) */
    const GUEST_COOKIE = 'surl_gid';
    const COOKIE_TTL   = 60 * 24 * 365;

    protected NotificationService $notificationService;
    protected EmailService $emailService;

    public function __construct(NotificationService $notificationService, EmailService $emailService)
    {
        $this->notificationService = $notificationService;
        $this->emailService = $emailService;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Read the guest UUID from cookie or generate a fresh one.
     * Always re-queues the cookie to renew the TTL.
     */
    protected function resolveGuestId(Request $request): string
    {
        $gid = $request->cookie(self::GUEST_COOKIE);
        if (!$gid || strlen($gid) < 10) {
            $gid = (string) Str::uuid();
        }
        Cookie::queue(self::GUEST_COOKIE, $gid, self::COOKIE_TTL);
        return $gid;
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    /**
     * Display the landing page, passing the guest's own short links to the view.
     */
    public function index(Request $request)
    {
        $guestId    = $this->resolveGuestId($request);
        $totalGuest = ShortUrl::where('guest_id', $guestId)->count();
        $guestLinks = ShortUrl::where('guest_id', $guestId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($l) {
                $l->qr_code = $this->generateQrWithLogo($l->short_url);
                return $l;
            });

        return response()
            ->view('front.home', compact('guestLinks', 'totalGuest'))
            ->cookie(self::GUEST_COOKIE, $guestId, self::COOKIE_TTL);
    }

    // ── AJAX ──────────────────────────────────────────────────────────────────

    /**
     * Guest URL shortening via AJAX.
     * Reads/sets the browser cookie UUID, stores it as guest_id.
     */
    public function shorten(Request $request)
    {
        $request->validate([
            'url'          => 'required|url|max:2083',
            'custom_alias' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9-_]+$/',
                'unique:short_urls,custom_alias'
            ],
        ], [
            'url.required'       => 'Please enter a URL to shorten.',
            'url.url'            => 'Please enter a valid URL (include https://).',
            'custom_alias.unique' => 'This alias is already taken. Please choose another.',
            'custom_alias.regex' => 'Alias can only contain letters, numbers, dashes, and underscores.',
        ]);

        $guestId = $this->resolveGuestId($request);

        // If EXACT SAME URL and same alias scenario
        $existing = ShortUrl::where('original_url', $request->url)
            ->where('guest_id', $guestId)
            ->where('status', 'active');

        if ($request->filled('custom_alias')) {
            $existing->where('custom_alias', $request->custom_alias);
        } else {
            $existing->whereNull('custom_alias');
        }

        $existing = $existing->latest()->first();

        if ($existing) {
            $qrCode = $this->generateQrWithLogo($existing->short_url);

            return response()->json([
                'success'      => true,
                'short_url'    => $existing->short_url,
                'original_url' => $existing->original_url,
                'code'         => $existing->custom_alias ?: $existing->code,
                'clicks'       => $existing->clicks,
                'qr_code'      => (string) $qrCode,
                'message'      => 'Short URL already exists.',
            ])->cookie(self::GUEST_COOKIE, $guestId, self::COOKIE_TTL);
        }

        $shortUrl = ShortUrl::create([
            'original_url' => $request->url,
            'custom_alias' => $request->custom_alias,
            'status'       => 'active',
            'created_by'   => null,
            'guest_id'     => $guestId,
        ]);

        $qrCode = $this->generateQrWithLogo($shortUrl->short_url);

        return response()->json([
            'success'      => true,
            'short_url'    => $shortUrl->short_url,
            'original_url' => $shortUrl->original_url,
            'code'         => $shortUrl->custom_alias ?: $shortUrl->code,
            'clicks'       => 0,
            'qr_code'      => (string) $qrCode,
            'message'      => 'Short URL created successfully!',
        ])->cookie(self::GUEST_COOKIE, $guestId, self::COOKIE_TTL);
    }

    /**
     * Return the current guest's links as JSON (AJAX refresh — last 50).
     */
    public function myLinks(Request $request)
    {
        $guestId = $request->cookie(self::GUEST_COOKIE);

        if (!$guestId) {
            return response()->json(['links' => []]);
        }

        $query = ShortUrl::where('guest_id', $guestId);
        $total = $query->count();

        $links = $query
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($l) {
                $qrCode = $this->generateQrWithLogo($l->short_url);

                return [
                    'id'           => $l->id,
                    'short_url'    => $l->short_url,
                    'original_url' => $l->original_url,
                    'code'         => $l->custom_alias ?: $l->code,
                    'clicks'       => $l->clicks,
                    'status'       => $l->status,
                    'created_at'   => $l->created_at->diffForHumans(),
                    'qr_code'      => (string) $qrCode,
                ];
            });

        return response()->json([
            'links' => $links,
            'total' => $total,
        ]);
    }


    /**
     * DataTables AJAX source for guest links full page.
     */
    public function guestLinksData(Request $request)
    {
        $guestId = $request->cookie(self::GUEST_COOKIE);

        if (!$guestId) {
            return response()->json([
                'draw'            => intval($request->draw),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ]);
        }

        $query = ShortUrl::where('guest_id', $guestId);

        // Status filter (sent as extra `data` param from DataTables)
        $status = $request->input('status');
        if ($status && in_array($status, ['active', 'inactive', 'expired'])) {
            $query->where('status', $status);
        }

        // Search
        $search = $request->input('search.value');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('original_url', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('custom_alias', 'like', "%{$search}%");
            });
        }

        $total    = ShortUrl::where('guest_id', $guestId)->count();
        $filtered = $query->count();

        // Order — columns: 0=created_at, 1=short_url(NA), 2=original_url, 3=clicks, 4=status(NA), 5=actions(NA)
        $orderCol = intval($request->input('order.0.column', 0));
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $cols     = [0 => 'created_at', 2 => 'original_url', 3 => 'clicks'];
        $query->orderBy($cols[$orderCol] ?? 'created_at', $orderDir);

        $rows = $query
            ->offset(intval($request->input('start', 0)))
            ->limit(intval($request->input('length', 25)))
            ->get()
            ->map(function ($l) {
                $qrCode = $this->generateQrWithLogo($l->short_url);

                return [
                    'created_at'   => $l->created_at->format('M d, Y H:i'),
                    'short_url'    => $l->short_url,
                    'original_url' => $l->original_url,
                    'code'         => $l->custom_alias ?: $l->code,
                    'clicks'       => $l->clicks,
                    'status'       => $l->status,
                    'qr_code'      => (string) $qrCode,
                ];
            });

        return response()->json([
            'draw'            => intval($request->draw),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ]);
    }

    /**
     * Redirect a short code / alias to the original URL.
     */
    public function redirect(string $code)
    {
        $shortUrl = ShortUrl::where('custom_alias', $code)
            ->orWhere('code', $code)
            ->first();

        if (!$shortUrl || $shortUrl->status !== 'active') {
            abort(404);
        }

        if ($shortUrl->isExpired()) {
            $shortUrl->update(['status' => 'expired']);
            abort(410, 'This short URL has expired.');
        }

        $shortUrl->increment('clicks');

        try {
            ShortUrlClick::create(
                ShortUrlClick::fromRequest(request(), $shortUrl->id)
            );
        } catch (\Exception $e) {
            Log::warning('ShortUrlClick log failed: ' . $e->getMessage());
        }

        return redirect()->away($shortUrl->original_url);
    }

    /**
     * Handle contact form submission.
     */
    public function sendContact(ContactRequest $request)
    {
        try {
            $contact = Contact::create([
                'name'    => $request->name,
                'email'   => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'status'  => 'new',
                'is_spam' => false,
            ]);

            $this->emailService->sendContactThankYou(
                $contact->email,
                $contact->name,
                $contact->subject,
                $contact->message
            );

            $this->notificationService->sendToSuperAdmins(
                'contact_received',
                "New Contact Message from {$contact->name}",
                "{$contact->name} ({$contact->email}) sent a message with subject: {$contact->subject}",
                [
                    'contact_id'    => $contact->id,
                    'contact_name'  => $contact->name,
                    'contact_email' => $contact->email,
                    'subject'       => $contact->subject,
                    'url'           => route('admin.contacts.show', $contact->id),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message! We will get back to you soon.',
            ]);
        } catch (\Exception $e) {
            Log::error('Contact form submission error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an error sending your message. Please try again later.',
            ], 500);
        }
    }

    /**
     * Generates an SVG QR code with an embedded PNG logo in the center.
     */
    private function generateQrWithLogo($url)
    {
        $qrSize = 100;
        $qrCode = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::size($qrSize)
            ->errorCorrection('H')
            ->generate($url);

        $logoPath = public_path('build/images/logo-mini-dark.png');
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $bgSize = $qrSize * 0.28;
            $bgOffset = ($qrSize - $bgSize) / 2;
            $logoSize = $qrSize * 0.22;
            $logoOffset = ($qrSize - $logoSize) / 2;

            $bgTag = sprintf(
                '<rect x="%f" y="%f" width="%f" height="%f" fill="#ffffff" rx="3" />',
                $bgOffset,
                $bgOffset,
                $bgSize,
                $bgSize
            );

            $imageTag = sprintf(
                '<image x="%f" y="%f" width="%f" height="%f" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="data:image/png;base64,%s" href="data:image/png;base64,%s" preserveAspectRatio="xMidYMid slice" />',
                $logoOffset,
                $logoOffset,
                $logoSize,
                $logoSize,
                $logoData,
                $logoData
            );

            // Insert tags before closing `</svg>`
            $qrCode = str_replace('</svg>', $bgTag . $imageTag . '</svg>', $qrCode);
        }

        return $qrCode;
    }
}

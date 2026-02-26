@props(['link'])

@php
// Defaults matching the data
$sc = [
'active'=>'success',
'inactive'=>'secondary',
'expired'=>'danger'
];
$statusColor = $sc[$link->status ?? 'secondary'] ?? 'secondary';

// Original destination URL
$originalUrl = $link->original_url ?? '';
$originHost = '';
try {
$originHost = parse_url($originalUrl, PHP_URL_HOST) ?? '';
} catch (\Exception $e) {}
$originTrunc = strlen($originalUrl) > 55 ? substr($originalUrl, 0, 52) . '…' : $originalUrl;

// Short URL display
$shortUrl = rtrim(url('/'), '/') . '/' . ($link->custom_alias ?: $link->code);

// QR Code
$qrHtml = (string) ($link->qr_code ?? '');

// Check if extra classes are passed
$extraClasses = $attributes->get('class') ?? '';
@endphp

<div class="mock-result-row d-flex align-items-center gap-3 p-3 rounded-3 flex-column flex-sm-row bg-white border guest-link-card {{ $extraClasses }}" style="background: rgba(var(--bs-primary-rgb),.01) !important;">
    <div class="flex-shrink-0 bg-white p-1 rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width:48px;height:48px;border:1px solid rgba(0,0,0,.04);">
        <img src="https://www.google.com/s2/favicons?domain={{ $originHost }}&sz=128" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23999\' stroke-width=\'2\'><circle cx=\'12\' cy=\'12\' r=\'10\'/><line x1=\'2\' y1=\'12\' x2=\'22\' y2=\'12\'/><path d=\'M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z\'/></svg>'" alt="icon" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
    </div>

    <div class="flex-grow-1 text-center text-sm-start overflow-hidden">
        <div style="font-size:.7rem;opacity:.5;margin-bottom:2px;" class="fw-semibold text-truncate" title="{{ $originalUrl }}">{{ $originTrunc }}</div>
        <div class="fw-bold text-truncate text-primary" style="font-size:.9rem;">
            {{ $shortUrl }}
        </div>
        <div class="d-flex align-items-center gap-2 mt-1 justify-content-center justify-content-sm-start flex-wrap">
            <span class="badge bg-{{ $statusColor }} bg-opacity-15 font-monospace" style="font-size:.65rem;font-weight:700;">
                {{ strtoupper($link->status ?? 'unknown') }}
            </span>
            <span class="text-muted" style="font-size:.7rem;"><i data-lucide="bar-chart-2" style="width:11px;height:11px;display:inline-block;"></i> {{ number_format($link->clicks ?? 0) }} clicks</span>
            <span class="text-muted" style="font-size:.7rem;">• {{ $link->created_at ? $link->created_at->diffForHumans() : '' }}</span>
        </div>
    </div>

    <div class="d-flex gap-2 flex-shrink-0">
        <button class="mock-action-btn guest-copy-btn gl-copy" data-url="{{ $link->short_url }}" title="Copy">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
            </svg>
        </button>
        <button type="button" class="mock-action-btn guest-qr-btn gl-qr" data-url="{{ $link->short_url }}" data-code="{{ $link->custom_alias ?: $link->code }}" data-qr="{{ $qrHtml }}" title="QR Code">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1" />
                <rect x="14" y="3" width="7" height="7" rx="1" />
                <rect x="14" y="14" width="7" height="7" rx="1" />
                <rect x="3" y="14" width="7" height="7" rx="1" />
            </svg>
        </button>
        <a href="{{ $link->short_url }}" target="_blank" class="mock-action-btn" title="Open">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                <polyline points="15 3 21 3 21 9" />
                <line x1="10" y1="14" x2="21" y2="3" />
            </svg>
        </a>
    </div>
</div>
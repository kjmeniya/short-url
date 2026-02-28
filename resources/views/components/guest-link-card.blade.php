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

<div class="mock-result-row d-flex align-items-center gap-3 p-3 rounded-3 guest-link-card {{ $extraClasses }} position-relative bg-white">
    <div class="flex-shrink-0 bg-white p-1 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;border:1px solid rgba(0,0,0,.04);">
        <img src="https://www.google.com/s2/favicons?domain={{ $originHost }}&sz=128" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23999\' stroke-width=\'2\'><circle cx=\'12\' cy=\'12\' r=\'10\'/><line x1=\'2\' y1=\'12\' x2=\'22\' y2=\'12\'/><path d=\'M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z\'/></svg>'" alt="icon" class="w-100 h-100 object-fit-cover rounded-circle">
    </div>

    <div class="flex-grow-1 text-start overflow-hidden">
        <div style="font-size:.7rem;opacity:.5;margin-bottom:2px;" class="fw-semibold text-truncate" title="{{ $originalUrl }}">{{ $originTrunc }}</div>
        <div class="fw-bold text-truncate text-primary" style="font-size:.9rem;">
            {{ $shortUrl }}
        </div>

    </div>
    <div class="d-flex gap-2 flex-shrink-0">
        <div class="align-items-center gap-2 mt-1 justify-content-center justify-content-sm-start flex-wrap d-none d-sm-flex">
            <span class="badge bg-{{ $statusColor }} bg-opacity-15">
                {{ ucfirst($link->status ?? 'unknown') }}
            </span>
            <span class="text-muted" style="font-size:.7rem;"><i data-lucide="bar-chart-2" style="width:11px;height:11px;display:inline-block;"></i> {{ number_format($link->clicks ?? 0) }} clicks</span>
            <span class="text-muted" style="font-size:.7rem;">• {{ $link->created_at ? $link->created_at->diffForHumans() : '' }}</span>
        </div>
        <div class="d-flex gap-2 flex-shrink-0">
            <button class="mock-action-btn guest-copy-btn gl-copy" data-url="{{ $link->short_url }}" title="Copy">
                <i data-lucide="copy" class="icon-sm"></i>
            </button>
            <button type="button" class="mock-action-btn guest-qr-btn gl-qr" data-url="{{ $link->short_url }}" data-code="{{ $link->custom_alias ?: $link->code }}" data-qr="{{ $qrHtml }}" title="QR Code">
                <i data-lucide="qr-code" class="icon-sm"></i>
            </button>
            <a href="{{ $link->short_url }}" target="_blank" class="mock-action-btn" title="Open">
                <i data-lucide="external-link" class="icon-sm"></i>
            </a>
        </div>
    </div>
    <span class="badge bg-{{ $statusColor }} bg-opacity-15 position-absolute top-0 end-0 d-sm-none">
        {{ ucfirst($link->status ?? 'unknown') }}
    </span>
</div>
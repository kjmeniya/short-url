<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting... | {{ \App\Models\Setting::get('site_name', 'ShortURL') }}</title>
    <!-- Include core CSS -->
    <link href="{{ asset('build/css/app.css') }}" rel="stylesheet">
    <link href="{{ route('theme.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: var(--bs-font-sans-serif);
        }
        .delay-card {
            background: #fff;
            padding: 3rem 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .countdown {
            font-size: 4rem;
            font-weight: 800;
            color: var(--bs-primary);
            line-height: 1;
            margin: 1.5rem 0;
        }
        .spinner-wrapper {
            position: relative;
            display: inline-block;
        }
        .pulse-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 120px;
            border: 3px solid var(--bs-primary);
            border-radius: 50%;
            animation: pulse 1.5s infinite ease-out;
            opacity: 0;
        }
        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.8; }
            100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
        }
        .destination-box {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 2rem;
            word-break: break-all;
            font-size: 0.875rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="delay-card">
        <h4 class="mb-2 fw-bold text-dark">You are being redirected</h4>
        <p class="text-muted mb-4">Please wait while we prepare your destination link.</p>
        
        <div class="spinner-wrapper mb-2">
            <div class="pulse-ring"></div>
            <div class="countdown" id="timer">{{ $delay }}</div>
        </div>
        
        <p class="text-muted">seconds remaining</p>

        <div class="destination-box">
            <span class="d-block fw-semibold text-dark mb-1">Destination:</span>
            {{ $targetUrl }}
        </div>
        
        <div class="mt-4">
            <a href="{{ $targetUrl }}" class="btn btn-primary d-none" id="skipBtn">Skip & Continue</a>
        </div>
    </div>

    <!-- Fallback meta refresh in case JS is disabled -->
    <noscript>
        <meta http-equiv="refresh" content="{{ $delay }};url={{ $targetUrl }}">
    </noscript>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let seconds = {{ $delay }};
            const timerEl = document.getElementById('timer');
            const skipBtn = document.getElementById('skipBtn');
            const targetUrl = "{{ $targetUrl }}";

            // Allow skipping after half the time passes (optional UX feature, disabled by default, we just show a manual button if we want, but wait user requested delay. Let's just automatically redirect.)
            // We just start countdown.
            
            const interval = setInterval(() => {
                seconds--;
                timerEl.textContent = seconds;
                
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = targetUrl;
                }
            }, 1000);
        });
    </script>
</body>
</html>

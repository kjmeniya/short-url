<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $shortUrl->og_title ?? config('app.name') }}</title>
    
    <link rel="canonical" href="{{ $shortUrl->short_url }}" />
    
    <!-- Open Graph/Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ $shortUrl->short_url }}" />
    <meta property="og:title" content="{{ $shortUrl->og_title }}" />
    @if($shortUrl->og_description)
    <meta property="og:description" content="{{ $shortUrl->og_description }}" />
    @endif
    @if($shortUrl->og_image)
    <meta property="og:image" content="{{ $shortUrl->og_image }}" />
    @endif

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="{{ $shortUrl->short_url }}" />
    <meta property="twitter:title" content="{{ $shortUrl->og_title }}" />
    @if($shortUrl->og_description)
    <meta property="twitter:description" content="{{ $shortUrl->og_description }}" />
    @endif
    @if($shortUrl->og_image)
    <meta property="twitter:image" content="{{ $shortUrl->og_image }}" />
    @endif
</head>
<body>
    <script>
        window.location.replace("{{ $shortUrl->short_url }}");
    </script>
</body>
</html>

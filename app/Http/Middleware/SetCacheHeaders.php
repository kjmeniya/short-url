<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Don't cache if user is authenticated or it's an admin route
        if (Auth::check() || $request->is('admin/*') || $request->is('api/*')) {
            return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        // Cache static assets for 1 year
        if ($this->isStaticAsset($request)) {
            return $response->header('Cache-Control', 'public, max-age=31536000, immutable')
                ->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }

        // Cache HTML pages for 1 hour
        if ($this->isHtmlPage($response)) {
            return $response->header('Cache-Control', 'public, max-age=3600, must-revalidate')
                ->header('Expires', gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        }

        // Default: no cache for dynamic content
        return $response->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Check if the request is for a static asset
     */
    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        $staticExtensions = [
            'css',
            'js',
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'svg',
            'ico',
            'woff',
            'woff2',
            'ttf',
            'otf',
            'eot',
            'mp4',
            'webm',
            'mp3',
            'pdf'
        ];

        foreach ($staticExtensions as $ext) {
            if (str_ends_with($path, '.' . $ext)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the response is HTML
     */
    private function isHtmlPage(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html');
    }
}

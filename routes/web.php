<?php

use App\Http\Controllers\Front\BlogController;
use App\Http\Controllers\Front\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Frontend Routes
Route::middleware(['maintenance'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('front.home');
    Route::post('/contact', [HomeController::class, 'sendContact'])->name('front.contact.send');

    // Guest URL shortening (AJAX)
    Route::post('/shorten', [HomeController::class, 'shorten'])->name('front.shorten');
    Route::get('/links', [HomeController::class, 'myLinks'])->name('front.links');

    // Pricing Page
    Route::get('/pricing', [HomeController::class, 'pricing'])->name('front.pricing');

    // Guest links DataTables AJAX (used in home modal)
    Route::get('/guest/links/data', [HomeController::class, 'guestLinksData'])->name('front.guest-links.data');

    // Blog Routes
    Route::get('/blogs', [BlogController::class, 'blogIndex'])->name('front.blogs.index');
    Route::get('/blog/{slug}', [BlogController::class, 'showBlog'])->name('front.blogs.show');

    // Password verification for protected links (5 attempts per minute)
    Route::post('/{code}/verify-password', [HomeController::class, 'verifyPassword'])
        ->middleware('throttle:5,1')
        ->name('front.password.verify');

    // Short-link redirect — must be LAST to avoid swallowing named routes
    Route::get('/{code}', [HomeController::class, 'redirect'])
        ->name('front.redirect')
        ->where('code', '[a-zA-Z0-9_-]+');
});

// Dynamic CSS for theme colors
Route::get('/css/theme.css', function () {
    $settingsService = app(\App\Services\SettingsService::class);
    function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "$r, $g, $b";
    }
    // Get theme colors from settings or use defaults from _variables.scss
    $primary = $settingsService->get('primary_color', '#245dac');
    $secondary = $settingsService->get('secondary_color', '#6c757d');
    $success = $settingsService->get('success_color', '#198754');
    $danger = $settingsService->get('danger_color', '#dc3545');
    $warning = $settingsService->get('warning_color', '#ffc107');
    $info = $settingsService->get('info_color', '#0dcaf0');
    $light = $settingsService->get('light_color', '#e9ecef');
    $dark = $settingsService->get('dark_color', '#212529');

    $css = "
    :root {
        --bs-primary: {$primary};
        --bs-primary-rgb: " . hexToRgb($primary) . ";

        --bs-secondary: {$secondary};
        --bs-secondary-rgb: " . hexToRgb($secondary) . ";

        --bs-success: {$success};
        --bs-success-rgb: " . hexToRgb($success) . ";

        --bs-danger: {$danger};
        --bs-danger-rgb: " . hexToRgb($danger) . ";

        --bs-warning: {$warning};
        --bs-warning-rgb: " . hexToRgb($warning) . ";

        --bs-info: {$info};
        --bs-info-rgb: " . hexToRgb($info) . ";

        --bs-light: {$light};
        --bs-light-rgb: " . hexToRgb($light) . ";

        --bs-dark: {$dark};
        --bs-dark-rgb: " . hexToRgb($dark) . ";

        --bs-link-color: {$primary};
        --bs-link-hover-color: color-mix(in srgb, var(--bs-link-color) 85%, black);

        --font-family-base: {$settingsService->get('font_family', 'Roboto, sans-serif')};
    }

    body {
        font-family: var(--font-family-base);
    }

    .btn-primary {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }

    .btn-primary:hover {
        background-color: color-mix(in srgb, var(--bs-primary) 85%, black);
        border-color: color-mix(in srgb, var(--bs-primary) 85%, black);
    }

    .text-primary {
        color: var(--bs-primary) !important;
    }

    .bg-primary {
        background-color: rgba(var(--bs-primary-rgb), var(--bs-bg-opacity)) !important;
    }

    .border-primary {
        border-color: var(--bs-primary) !important;
    }

    .nav-link.active {
        color: var(--bs-primary) !important;
    }
    ";

    return response($css, 200, [
        'Content-Type' => 'text/css',
        'Cache-Control' => 'public, max-age=3600'
    ]);
})->name('theme.css');

/*
|--------------------------------------------------------------------------
| Include Route Files
|--------------------------------------------------------------------------
|
| Authentication and Admin routes are separated into their own files
| for better organization and maintainability.
|
*/

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/user.php';

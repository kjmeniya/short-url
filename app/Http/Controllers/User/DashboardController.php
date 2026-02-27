<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    /**
     * Show the user dashboard with link statistics.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user   = Auth::user();
        $userId = $user->id;

        $stats = [
            'total'        => ShortUrl::where('created_by', $userId)->count(),
            'active'       => ShortUrl::where('created_by', $userId)->where('status', 'active')->count(),
            'inactive'     => ShortUrl::where('created_by', $userId)->where('status', 'inactive')->count(),
            'total_clicks' => ShortUrl::where('created_by', $userId)->sum('clicks'),
            'this_month'   => ShortUrl::where('created_by', $userId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $recentLinks = ShortUrl::where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart: clicks per day last 7 days
        $chartLabels = [];
        $chartClicks = [];
        for ($i = 6; $i >= 0; $i--) {
            $date          = now()->subDays($i);
            $chartLabels[] = $date->format('D, M j');
            $chartClicks[] = ShortUrl::where('created_by', $userId)
                ->whereDate('created_at', $date->toDateString())
                ->sum('clicks');
        }

        $chartData = [
            'labels' => $chartLabels,
            'clicks' => $chartClicks,
        ];

        return view('user.dashboard.index', compact('stats', 'recentLinks', 'chartData'));
    }



    /**
     * Show the user's profile.
     */
    public function profile()
    {
        $user = Auth::user();
        return view('user.dashboard.profile', compact('user'));
    }

    /**
     * Update the user's profile.
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:' . password_min_length(), 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = ['name' => $request->name];

        if ($request->filled('password')) {
            $data['password']            = Hash::make($request->password);
            $data['password_changed_at'] = now();
        }

        $user->update($data);

        return redirect()->route('user.profile')
            ->with('success', 'Profile updated successfully.');
    }
}

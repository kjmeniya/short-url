<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MyLinkController extends Controller
{
    /**
     * Paginated list of all user links.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $search = $request->get('search');
        $status = $request->get('status');

        $query = ShortUrl::where('created_by', $userId)->orderBy('created_at', 'desc');

        if ($search) {
            $query->search($search);
        }

        if ($status && in_array($status, ['active', 'inactive', 'expired'])) {
            $query->where('status', $status);
        }

        $links = $query->paginate(15)->withQueryString();

        return view('user.dashboard.my-links', compact('links', 'search', 'status'));
    }

    /**
     * Store a new short URL from the user panel.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'original_url' => ['required', 'url', 'max:2048'],
            'title'        => ['nullable', 'string', 'max:255'],
            'custom_alias' => ['nullable', 'string', 'max:50', 'alpha_dash',
                'unique:short_urls,custom_alias'],
            'expires_at'   => ['nullable', 'date', 'after:now'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ShortUrl::create([
            'original_url' => $request->original_url,
            'title'        => $request->title,
            'custom_alias' => $request->custom_alias ?: null,
            'expires_at'   => $request->expires_at,
            'status'       => 'active',
            'created_by'   => Auth::id(),
            'updated_by'   => Auth::id(),
        ]);

        return redirect()->route('user.my-links')
            ->with('success', 'Short link created successfully!');
    }

    /**
     * Delete user's own short URL.
     */
    public function destroy(int $id)
    {
        $link = ShortUrl::where('id', $id)
            ->where('created_by', Auth::id())
            ->firstOrFail();

        $link->delete();

        return redirect()->back()->with('success', 'Link deleted successfully.');
    }

    /**
     * Toggle status of user's own link.
     */
    public function toggle(int $id)
    {
        $link = ShortUrl::where('id', $id)
            ->where('created_by', Auth::id())
            ->firstOrFail();

        $link->update([
            'status' => $link->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()->back()->with('success', 'Link status updated.');
    }
}

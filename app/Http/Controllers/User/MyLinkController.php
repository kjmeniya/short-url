<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShortUrlRequest;
use App\Http\Requests\UpdateShortUrlRequest;
use App\Services\ShortUrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyLinkController extends Controller
{
    public function __construct(protected ShortUrlService $service)
    {
    }

    /**
     * Paginated list of the authenticated user's links.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $links = $this->service->paginate(
            search:  $search,
            status:  $status,
            ownerId: Auth::id(),
        );

        $stats = $this->service->getStats(Auth::id());

        return view('user.dashboard.my-links', compact('links', 'search', 'status', 'stats'));
    }

    /**
     * Show the "Create new link" form.
     */
    public function create()
    {
        return view('user.dashboard.create-link');
    }

    /**
     * Persist a newly created link owned by the current user.
     */
    public function store(StoreShortUrlRequest $request)
    {
        $this->service->create($request->validated(), Auth::id());

        return redirect()->route('user.my-links')
            ->with('success', 'Short link created successfully!');
    }

    /**
     * Show the edit form for a link owned by the current user.
     */
    public function edit(int $id)
    {
        $link = $this->service->findOrFail($id, Auth::id());

        return view('user.dashboard.edit-link', compact('link'));
    }

    /**
     * Persist updates to a link owned by the current user.
     */
    public function update(UpdateShortUrlRequest $request, int $id)
    {
        $link = $this->service->findOrFail($id, Auth::id());

        $this->service->update($link, $request->validated(), Auth::id());

        return redirect()->route('user.my-links')
            ->with('success', 'Link updated successfully!');
    }

    /**
     * Delete a link owned by the current user.
     */
    public function destroy(int $id)
    {
        $link = $this->service->findOrFail($id, Auth::id());

        $this->service->delete($link);

        return redirect()->back()->with('success', 'Link deleted successfully.');
    }

    /**
     * Toggle active ↔ inactive for a link owned by the current user.
     */
    public function toggle(int $id)
    {
        $link = $this->service->findOrFail($id, Auth::id());

        $this->service->toggleStatus($link, Auth::id());

        return redirect()->back()->with('success', 'Link status updated.');
    }

    /**
     * AJAX: check if a slug is available (and not reserved).
     * GET /user/links/check-slug?slug=foo&exclude_id=5
     */
    public function checkSlug(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->service->checkSlugAvailability(
            slug:      (string) $request->input('slug', ''),
            excludeId: $request->integer('exclude_id') ?: null,
        );
    }
}

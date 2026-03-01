<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Traits\AdminSeoTrait;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubscriptionController extends Controller
{
    use AdminSeoTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Subscription::with(['user', 'plan'])->select('subscriptions.*');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('plan_id')) {
                $query->where('plan_id', $request->plan_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->user ? $row->user->name : 'N/A';
                })
                ->addColumn('user_email', function ($row) {
                    return $row->user ? $row->user->email : 'N/A';
                })
                ->addColumn('plan_name', function ($row) {
                    return $row->plan ? $row->plan->name : 'N/A';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->status === 'active'
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Cancelled/Expired</span>';
                })
                ->editColumn('starts_at', function ($row) {
                    return $row->starts_at ? $row->starts_at->format('M d, Y') : 'N/A';
                })
                ->editColumn('ends_at', function ($row) {
                    return $row->ends_at ? $row->ends_at->format('M d, Y') : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-sm" data-lucide="more-horizontal"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="' . route('admin.subscriptions.show', $row->id) . '">
                                    <i class="icon-sm me-2" data-lucide="eye"></i>View
                                </a>
                            </li>';

                    if ($row->status === 'active') {
                        $actions .= '<li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="' . route('admin.subscriptions.destroy', $row->id) . '" method="POST" class="d-inline subscription-cancel-form">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="button" class="dropdown-item text-danger cancel-subscription">
                                        <i class="icon-sm me-2" data-lucide="x-circle"></i>Cancel
                                    </button>
                                </form>
                            </li>';
                    }
                    $actions .= '</ul></div>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
        ];

        $plans = \App\Models\Plan::all();

        $viewData = $this->withSeo(
            compact('stats', 'plans'),
            'Subscription Management',
            'Manage user subscriptions.',
            'subscriptions, billing, management'
        );

        return view('admin.subscriptions.index', $viewData);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);

        $viewData = $this->withSeo(
            compact('subscription'),
            'Subscription Details',
            'View user subscription details.',
            'subscription details, view subscription'
        );

        return view('admin.subscriptions.show', $viewData);
    }

    /**
     * Remove the specified resource from storage (cancel subscription).
     */
    public function destroy($id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            $subscription->update(['status' => 'cancelled', 'ends_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling subscription: ' . $e->getMessage()
            ], 500);
        }
    }
}

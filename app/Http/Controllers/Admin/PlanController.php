<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Traits\AdminSeoTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    use AdminSeoTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Plan::select('plans.*');

            if ($request->filled('status')) {
                $status = $request->status === 'active' ? 1 : 0;
                $query->where('is_active', $status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-sm" data-lucide="more-horizontal"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="' . route('admin.plans.show', $row->id) . '">
                                    <i class="icon-sm me-2" data-lucide="eye"></i>View
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="' . route('admin.plans.edit', $row->id) . '">
                                    <i class="icon-sm me-2" data-lucide="edit"></i>Edit
                                </a>
                            </li>';

                    $actions .= '<li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger delete-plan" href="javascript:void(0)" data-id="' . $row->id . '">
                                    <i class="icon-sm me-2" data-lucide="trash-2"></i>Delete
                                </a>
                            </li>
                        </ul>
                    </div>';
                    return $actions;
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->editColumn('price', function ($row) {
                    return '$' . number_format($row->price, 2);
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('M d, Y g:i A');
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        $stats = [
            'total' => Plan::count(),
            'active' => Plan::where('is_active', true)->count(),
            'inactive' => Plan::where('is_active', false)->count(),
        ];

        $viewData = $this->withSeo(
            compact('stats'),
            'Plan Management',
            'Manage subscription plans and features.',
            'plans, subscriptions, management'
        );

        return view('admin.plans.index', $viewData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $viewData = $this->withSeo(
            [],
            'Create Plan',
            'Create new subscription plan.',
            'create plan, add plan'
        );

        return view('admin.plans.create', $viewData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:plans,name',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'features' => 'nullable|array',
            'features.*.name' => 'required|string|max:255',
            'features.*.value' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below and try again.');
        }

        $validated = $validator->validated();

        $plan = Plan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $validated['is_active'],
        ]);

        if (isset($validated['features']) && count($validated['features']) > 0) {
            foreach ($validated['features'] as $feature) {
                if (!empty($feature['name'])) {
                    PlanFeature::create([
                        'plan_id' => $plan->id,
                        'feature_name' => $feature['name'],
                        'feature_value' => $feature['value'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $plan = Plan::with('features')->findOrFail($id);

        $viewData = $this->withSeo(
            compact('plan'),
            'Plan Details',
            'View subscription plan details.',
            'plan details, view plan'
        );

        return view('admin.plans.show', $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $plan = Plan::with('features')->findOrFail($id);

        $viewData = $this->withSeo(
            compact('plan'),
            'Edit Plan',
            'Edit subscription plan.',
            'edit plan, update plan'
        );

        return view('admin.plans.edit', $viewData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:plans,name,' . $plan->id,
            'slug' => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'features' => 'nullable|array',
            'features.*.name' => 'required|string|max:255',
            'features.*.value' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below and try again.');
        }

        $validated = $validator->validated();

        $plan->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $validated['is_active'],
        ]);

        // Delete existing features and recreate them
        $plan->features()->delete();

        if (isset($validated['features']) && count($validated['features']) > 0) {
            foreach ($validated['features'] as $feature) {
                if (!empty($feature['name'])) {
                    PlanFeature::create([
                        'plan_id' => $plan->id,
                        'feature_name' => $feature['name'],
                        'feature_value' => $feature['value'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $plan = Plan::findOrFail($id);
            $plan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plan deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting plan: ' . $e->getMessage()
            ], 500);
        }
    }
}

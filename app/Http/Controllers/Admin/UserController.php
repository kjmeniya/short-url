<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\FileUploadService;
use App\Services\TwoFactorAuthService;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    protected NotificationService $notificationService;
    protected FileUploadService $fileUploadService;

    public function __construct(NotificationService $notificationService, FileUploadService $fileUploadService)
    {
        $this->notificationService = $notificationService;
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get users visible to current user (excluding soft deleted)
            $users = User::visibleTo(Auth::user())
                ->with('role:id,name,display_name')
                ->select(['id', 'avatar', 'name', 'email', 'role_id', 'is_active', 'email_verified_at', 'created_at', 'is_google_user', 'google_id', 'locked_until']);

            // Apply filters
            if ($request->filled('role')) {
                $users->whereHas('role', function ($roleQuery) use ($request) {
                    $roleQuery->where('name', $request->role);
                });
            }

            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'active':
                        $users->where('is_active', true)->whereNotNull('email_verified_at');
                        break;
                    case 'inactive':
                        $users->where('is_active', false);
                        break;
                    case 'unverified':
                        $users->whereNull('email_verified_at');
                        break;
                    case 'locked':
                        $users->whereNotNull('locked_until')
                            ->where('locked_until', '>', now());
                        break;
                }
            }

            if ($request->filled('google_user')) {
                switch ($request->google_user) {
                    case 'yes':
                        $users->where(function ($query) {
                            $query->where('is_google_user', true)
                                ->orWhereNotNull('google_id');
                        });
                        break;
                    case 'no':
                        $users->where(function ($query) {
                            $query->where('is_google_user', false)
                                ->whereNull('google_id');
                        });
                        break;
                }
            }

            // Apply date filter
            $users = $this->applyDateFilter($users, $request);

            return DataTables::of($users)
                ->addColumn('avatar', function ($user) {
                    if ($user->hasAvatar()) {
                        return '<img src="' . $user->avatar_url . '" alt="Avatar" class="w-30px h-30px rounded-circle" width="30" height="30" style="object-fit: cover;">';
                    } else {
                        return '<div class="w-30px h-30px bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="font-size: 12px; font-weight: 600;">
                                    ' . $user->initials . '
                                </div>';
                    }
                })
                ->addColumn('name_email', function ($user) {
                    return '<strong>' . e($user->name) . '</strong><br><small class="text-muted">' . e($user->email) . '</small>';
                })
                ->addColumn('role', function ($user) {
                    // Use new role system only
                    if ($user->role_id && $user->role) {
                        $roleName = $user->role->name;
                        $roleLabel = $user->role->display_name;

                        $roleColors = [
                            'user' => 'bg-primary',
                            'admin' => 'bg-warning',
                            'super_admin' => 'bg-danger'
                        ];
                        $color = $roleColors[$roleName] ?? 'bg-secondary';
                        return '<span class="badge ' . $color . '">' . $roleLabel . '</span>';
                    }

                    return '<span class="badge bg-secondary">No Role</span>';
                })
                ->addColumn('status', function ($user) {
                    if (!$user->is_active) {
                        return '<span class="badge bg-danger">Inactive</span>';
                    } elseif (!$user->email_verified_at) {
                        return '<span class="badge bg-warning">Unverified</span>';
                    } else {
                        return '<span class="badge bg-success">Active</span>';
                    }
                })
                ->addColumn('google_user', function ($user) {
                    if ($user->isGoogleUser()) {
                        return '<div class="d-flex align-items-center">
                                    <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="-3 0 262 262" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027" fill="#4285F4"></path>
                                        <path d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1" fill="#34A853"></path>
                                        <path d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782" fill="#FBBC05"></path>
                                        <path d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251" fill="#EB4335"></path>
                                    </svg>
                                    <span class="badge bg-info d-none" title="Google User">Google</span>
                                </div>';
                    } else {
                        return '
                        <i class="text-muted icon-lg" data-lucide="shield" title="Regular User"></i>
                        <span class="badge bg-secondary d-none" title="Regular User">Regular</span>';
                    }
                })
                // ->addColumn('action', function ($user) {
                //     $actions = '<div class="d-flex gap-2" role="group">';
                //     $actions .= '<a href="' . route('admin.users.show', $user->id) . '" class="icon" title="View"><i class="text-success" data-lucide="eye"></i></a>';
                //     $actions .= '<a href="' . route('admin.users.edit', $user->id) . '" class="icon" title="Edit"><i class="text-primary icon-lg" data-lucide="edit"></i></a>';
                //     $actions .= '<a type="button" class="icon delete-user" data-id="' . $user->id . '" title="Delete"><i class="icon-lg text-danger" data-lucide="trash-2"></i></a>';
                //     $actions .= '</div>';
                //     return $actions;
                // })
                ->addColumn('action', function ($user) {
                    $currentUser = Auth::user();
                    $isLocked = $user->locked_until && now()->isBefore($user->locked_until);

                    $dropdown = '<div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="icon-sm" data-lucide="more-horizontal"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="' . route('admin.users.show', $user->id) . '">
                                        <i data-lucide="eye" class="icon-sm me-2 text-success"></i>View
                                    </a></li>
                                    <li><a class="dropdown-item" href="' . route('admin.users.edit', $user->id) . '">
                                        <i data-lucide="edit" class="icon-sm me-2 text-primary"></i>Edit
                                    </a></li>';

                    // Add unlock option for super admins if account is locked
                    if ($isLocked && $currentUser->isSuperAdmin()) {
                        $dropdown .= '<li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item unlock-account" href="#" data-id="' . $user->id . '" data-name="' . e($user->name) . '">
                                        <i data-lucide="unlock" class="icon-sm me-2 text-warning"></i>Unlock Account
                                    </a></li>';
                    }

                    $dropdown .= '      <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item delete-user" href="#" data-id="' . $user->id . '">
                                        <i data-lucide="trash-2" class="icon-sm me-2 text-danger"></i>Delete
                                    </a></li>
                                </ul>
                            </div>';

                    return $dropdown;
                })
                ->editColumn('created_at', function ($user) {
                    return formatUserDateTime($user->created_at);
                })
                ->addColumn('lock_message', function ($user) {
                    if ($user->locked_until && now()->isBefore($user->locked_until)) {
                        $remainingMinutes = (int) ceil(now()->diffInMinutes($user->locked_until, true));
                        $timeMessage = $remainingMinutes > 0
                            ? "{$remainingMinutes} minute" . ($remainingMinutes > 1 ? 's' : '')
                            : 'less than 1 minute';
                        return "Account is locked. Try again in {$timeMessage}";
                    }
                    return null;
                })
                ->rawColumns(['role', 'status', 'google_user', 'action', 'avatar', 'name_email'])
                ->make(true);
        }

        $roles = Role::active()->get();

        // Get statistics
        $stats = $this->getUserStats();

        $viewData = $this->withSeo(
            compact('roles', 'stats'),
            'Users',
            'Manage user accounts, roles, permissions and user information in the admin panel.',
            'users, accounts, profiles, user management, roles, permissions'
        );

        return view('admin.users.index', $viewData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Only show roles that the current user can assign
        $roles = $currentUser->role->getManageableRoles()->where('is_active', true)->get();

        $viewData = $this->withSeo(
            compact('roles'),
            'Create User',
            'Create new user accounts with role assignments and profile information.',
            'create user, new account, user registration, add user, roles'
        );

        return view('admin.users.create', $viewData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $validated = $request->validated();

        // Validate role assignment - user can only assign roles they can manage
        if (isset($validated['role_id'])) {
            $manageableRoleIds = $currentUser->role->getManageableRoles()->pluck('id')->toArray();
            if (!in_array($validated['role_id'], $manageableRoleIds)) {
                abort(403, 'You do not have permission to assign this role.');
            }
        }

        $validated['password'] = Hash::make($validated['password']);

        // Handle cropped avatar
        if ($request->filled('avatar_cropped')) {
            $validated['avatar'] = $this->fileUploadService->uploadBase64($request->input('avatar_cropped'), 'avatars');
        }

        $user = User::create($validated);

        // Send notification to admins
        $currentUser = Auth::user();
        $this->notificationService->sendToAdmins(
            'user_created',
            'New User Created',
            "User '{$user->name}' has been created by {$currentUser->name}",
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'created_by' => $currentUser->name,
                'url' => route('admin.users.show', $user->id)
            ]
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Check if current user can view this user
        if (!$user->canBeSeenBy($currentUser)) {
            abort(403, 'You do not have permission to view this user.');
        }

        $user->load('role');

        $viewData = $this->withSeo(
            compact('user'),
            'User Profile',
            "View detailed profile information for {$user->name}, including account details and settings.",
            'user profile, account details, user information, profile view'
        );

        return view('admin.users.show', $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Check if current user can manage this user
        if (!$user->canBeManagedBy($currentUser)) {
            abort(403, 'You do not have permission to edit this user.');
        }

        // Only show roles that the current user can assign
        $roles = $currentUser->role->getManageableRoles()->where('is_active', true)->get();

        $viewData = $this->withSeo(
            compact('user', 'roles'),
            'Edit User',
            "Edit account details, roles and permissions for {$user->name}.",
            'edit user, modify account, update profile, user settings, roles'
        );

        return view('admin.users.edit', $viewData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Check if current user can manage this user
        if (!$user->canBeManagedBy($currentUser)) {
            abort(403, 'You do not have permission to edit this user.');
        }

        $validated = $request->validated();

        // Validate role assignment - user can only assign roles they can manage
        if (isset($validated['role_id'])) {
            $manageableRoleIds = $currentUser->role->getManageableRoles()->pluck('id')->toArray();
            if (!in_array($validated['role_id'], $manageableRoleIds)) {
                abort(403, 'You do not have permission to assign this role.');
            }
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle avatar removal
        if ($request->input('avatar_remove') === '1') {
            // Delete old avatar if exists
            if ($user->avatar) {
                $this->fileUploadService->delete($user->avatar);
            }
            $validated['avatar'] = null;
        }
        // Handle cropped avatar (only if not removing)
        elseif ($request->filled('avatar_cropped')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                $this->fileUploadService->delete($user->avatar);
            }
            $validated['avatar'] = $this->fileUploadService->uploadBase64($request->input('avatar_cropped'), 'avatars');
        }

        // Store original values for comparison
        $originalRole = $user->role_id;
        $originalStatus = $user->is_active;

        $user->update($validated);

        // Send appropriate notifications based on what changed
        $currentUser = Auth::user();
        if (isset($validated['role_id']) && $originalRole !== $validated['role_id']) {
            $this->notificationService->sendToAdmins(
                'user_role_changed',
                'User Role Changed',
                "Role for user '{$user->name}' has been changed by {$currentUser->name}",
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'changed_by' => $currentUser->name,
                    'url' => route('admin.users.show', $user->id)
                ]
            );
        } elseif (isset($validated['is_active']) && $originalStatus !== $validated['is_active']) {
            $status = $validated['is_active'] ? 'activated' : 'deactivated';
            $this->notificationService->sendToAdmins(
                'user_status_changed',
                'User Status Changed',
                "User '{$user->name}' has been {$status} by {$currentUser->name}",
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'status' => $status,
                    'changed_by' => $currentUser->name,
                    'url' => route('admin.users.show', $user->id)
                ]
            );
        } else {
            $this->notificationService->sendToAdmins(
                'user_updated',
                'User Updated',
                "User '{$user->name}' has been updated by {$currentUser->name}",
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'updated_by' => $currentUser->name,
                    'url' => route('admin.users.show', $user->id)
                ]
            );
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Check if user can be deleted by current user
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Prevent self-deletion
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.'
            ], 403);
        }

        // Check if current user can manage this user (using role hierarchy)
        if (!$user->canBeManagedBy($currentUser)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this user.'
            ], 403);
        }

        // Get current user for tracking and notifications
        $currentUser = Auth::user();

        // Soft delete the user with tracking
        $user->softDeleteBy($currentUser);

        // Send notification to admins
        $this->notificationService->sendToAdmins(
            'user_deleted',
            'User Deleted',
            "User '{$user->name}' has been deleted by {$currentUser->name}",
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'deleted_by' => $currentUser->name,
                'url' => route('admin.users.index')
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.'
        ]);
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        // Check permissions
        /** @var User $currentUser */
        $currentUser = Auth::user();
        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to restore this user.'
            ], 403);
        }

        $user->restore();
        $user->update(['deleted_by' => null]);

        // Send notification to admins
        $currentUser = Auth::user();
        $this->notificationService->sendToAdmins(
            'user_restored',
            'User Restored',
            "User '{$user->name}' has been restored by {$currentUser->name}",
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'restored_by' => $currentUser->name,
                'url' => route('admin.users.show', $user->id)
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'User restored successfully.'
        ]);
    }

    /**
     * Permanently delete a user.
     */
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        // Check permissions
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Prevent self-deletion
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot permanently delete your own account.'
            ], 403);
        }

        // Only super admins can permanently delete users
        if (!$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only super administrators can permanently delete users.'
            ], 403);
        }

        // Super admins cannot be permanently deleted by anyone
        if ($user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Super administrators cannot be permanently deleted.'
            ], 403);
        }

        // Delete user avatar if exists
        if ($user->avatar) {
            $this->fileUploadService->delete($user->avatar);
        }

        // Store user info for notification before deletion
        $userName = $user->name;
        $userEmail = $user->email;

        // Permanently delete the user
        $user->forceDelete();

        // Send notification to admins
        $this->notificationService->sendToAdmins(
            'user_permanently_deleted',
            'User Permanently Deleted',
            "User '{$userName}' ({$userEmail}) has been permanently deleted by {$currentUser->name}",
            [
                'user_name' => $userName,
                'user_email' => $userEmail,
                'deleted_by' => $currentUser->name,
                'icon' => 'trash-2',
                'color' => 'danger',
                'url' => route('admin.users.trashed')
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'User permanently deleted successfully.'
        ]);
    }

    /**
     * Get trashed users.
     */
    public function trashed(Request $request)
    {
        if ($request->ajax()) {
            $users = User::onlyTrashed()
                ->with(['deletedBy:id,name', 'role:id,name,display_name'])
                ->select(['id', 'avatar', 'name', 'email', 'role_id', 'deleted_at', 'deleted_by']);

            // Apply filters
            if ($request->filled('role')) {
                $users->whereHas('role', function ($query) use ($request) {
                    $query->where('name', $request->role);
                });
            }

            if ($request->filled('deleted_by')) {
                if ($request->deleted_by === 'system') {
                    $users->whereNull('deleted_by');
                } else {
                    $users->whereHas('deletedBy', function ($query) use ($request) {
                        $query->where('role', $request->deleted_by);
                    });
                }
            }

            // Apply date filter
            $users = $this->applyDateFilter($users, $request, 'deleted_at');

            return DataTables::of($users)
                ->addColumn('avatar', function ($user) {
                    if ($user->hasAvatar()) {
                        return '<img src="' . $user->avatar_url . '" alt="Avatar" class="w-30px h-30px rounded-circle" width="30" height="30" style="object-fit: cover;">';
                    } else {
                        return '<div class="w-30px h-30px bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="font-size: 12px; font-weight: 600;">
                                    ' . $user->initials . '
                                </div>';
                    }
                })
                ->addColumn('role', function ($user) {
                    // Use new role system only
                    if ($user->role_id && $user->role) {
                        $roleName = $user->role->name;
                        $roleLabel = $user->role->display_name;

                        $roleColors = [
                            'user' => 'bg-primary',
                            'admin' => 'bg-warning',
                            'super_admin' => 'bg-danger'
                        ];
                        $color = $roleColors[$roleName] ?? 'bg-secondary';
                        return '<span class="badge ' . $color . '">' . $roleLabel . '</span>';
                    }

                    return '<span class="badge bg-secondary">No Role</span>';
                })
                ->addColumn('deleted_info', function ($user) {
                    $deletedBy = $user->deletedBy ? $user->deletedBy->name : 'System';
                    $deletedAt = $user->deleted_at ? formatUserDateTime($user->deleted_at) : 'Unknown';
                    return '<small class="text-muted">Deleted by: ' . $deletedBy . '<br>On: ' . $deletedAt . '</small>';
                })
                ->addColumn('action', function ($user) {
                    $currentUser = Auth::user();
                    $canPermanentlyDelete = $currentUser->isSuperAdmin() && !$user->isSuperAdmin();

                    $actions = '<div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-sm btn-outline-success restore-user" data-id="' . $user->id . '" title="Restore">
                            <i data-lucide="undo" class="icon-sm"></i>
                        </button>';

                    if ($canPermanentlyDelete) {
                        $actions .= '<button type="button" class="btn btn-sm btn-outline-danger force-delete-user" data-id="' . $user->id . '" title="Permanently Delete">
                            <i data-lucide="trash-2" class="icon-sm"></i>
                        </button>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['role', 'action', 'avatar', 'deleted_info'])
                ->make(true);
        }

        $viewData = $this->withSeo(
            [],
            'Deleted Users',
            'Manage soft-deleted user accounts with restore and permanent deletion options.',
            'deleted users, soft delete, restore users, trashed accounts, user management'
        );

        return view('admin.users.trashed', $viewData);
    }

    /**
     * Get user statistics.
     */
    private function getUserStats(): array
    {
        $total = User::count();
        $active = User::where('is_active', true)->count();
        $inactive = User::where('is_active', false)->count();
        $verified = User::whereNotNull('email_verified_at')->count();
        $unverified = User::whereNull('email_verified_at')->count();
        $admins = User::whereHas('role', function ($query) {
            $query->where('name', 'Super Admin');
        })->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'verified' => $verified,
            'unverified' => $unverified,
            'admins' => $admins,
            'active_rate' => $total > 0 ? round($active / $total * 100, 2) : 0,
        ];
    }

    /**
     * Enable two-factor authentication for a user.
     */
    public function enableTwoFactor(Request $request, User $user, TwoFactorAuthService $twoFactorService): JsonResponse
    {
        try {
            $request->validate([
                'method' => 'required|in:email,qr_code',
                'email_verification_code' => 'required_if:method,email|string|size:6',
                'verification_code' => 'required_if:method,qr_code|string|size:6',
            ]);

            $method = $request->input('method');
            $verificationCode = $method === 'email'
                ? $request->input('email_verification_code')
                : $request->input('verification_code');

            // Get secret from session for QR code method
            $secret = null;
            if ($method === 'qr_code') {
                $secret = session("temp_2fa_secret_user_{$user->id}");
                if (!$secret) {
                    return response()->json([
                        'success' => false,
                        'message' => 'QR code session expired. Please generate a new QR code.'
                    ], 400);
                }
            }

            if ($twoFactorService->enableTwoFactor($user, $method, $secret, $verificationCode)) {
                // Clear temporary secret
                session()->forget("temp_2fa_secret_user_{$user->id}");
                $recoveryCodes = $user->getTwoFactorRecoveryCodes();

                Log::info('Two-factor authentication enabled for user', [
                    'user_id' => $user->id,
                    'method' => $method,
                    'admin_id' => Auth::id()
                ]);

                // Send notification to super admins
                $currentUser = Auth::user();
                $this->notificationService->sendToSuperAdmins(
                    'user_2fa_enabled',
                    'Two-Factor Authentication Enabled',
                    "Two-factor authentication has been enabled for user '{$user->name}' by {$currentUser->name}",
                    [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'method' => $method,
                        'enabled_by' => $currentUser->name,
                        'url' => route('admin.users.show', $user->id)
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Two-factor authentication enabled successfully.',
                    'recovery_codes' => $recoveryCodes
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to enable two-factor authentication. Please check the verification code.'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error enabling two-factor authentication for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while enabling two-factor authentication.'
            ], 500);
        }
    }

    /**
     * Disable two-factor authentication for a user.
     */
    public function disableTwoFactor(User $user, TwoFactorAuthService $twoFactorService): JsonResponse
    {
        try {
            $twoFactorService->disableTwoFactor($user);

            Log::info('Two-factor authentication disabled for user', [
                'user_id' => $user->id,
                'admin_id' => Auth::id()
            ]);

            // Send notification to super admins
            $currentUser = Auth::user();
            $this->notificationService->sendToSuperAdmins(
                'user_2fa_disabled',
                'Two-Factor Authentication Disabled',
                "Two-factor authentication has been disabled for user '{$user->name}' by {$currentUser->name}",
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'disabled_by' => $currentUser->name,
                    'url' => route('admin.users.show', $user->id)
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication disabled successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error disabling two-factor authentication for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while disabling two-factor authentication.'
            ], 500);
        }
    }

    /**
     * Generate QR code for authenticator app setup.
     */
    public function generateQrCode(User $user, TwoFactorAuthService $twoFactorService): JsonResponse
    {
        try {
            // Generate new secret
            $secret = $twoFactorService->generateSecretKey();

            // Store secret in session temporarily with user ID to avoid conflicts
            session(["temp_2fa_secret_user_{$user->id}" => $secret]);

            // Generate QR code SVG
            $qrCodeSvg = $twoFactorService->generateQRCodeSvg($user, $secret);

            return response()->json([
                'success' => true,
                'qr_code' => $qrCodeSvg,
                'secret' => $secret
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating QR code for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code.'
            ], 500);
        }
    }

    /**
     * Send email verification code for two-factor setup.
     */
    public function sendEmailCode(User $user, TwoFactorAuthService $twoFactorService): JsonResponse
    {
        try {
            $twoFactorService->sendEmailCode($user);

            Log::info('Email verification code sent for user', [
                'user_id' => $user->id,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to user email address.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending email code for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code.'
            ], 500);
        }
    }

    /**
     * Regenerate recovery codes for a user.
     */
    public function regenerateRecoveryCodes(Request $request, User $user, TwoFactorAuthService $twoFactorService): JsonResponse
    {
        try {
            if (!$user->two_factor_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-factor authentication is not enabled for this user.'
                ], 400);
            }

            // Check if this is just a view request
            $viewOnly = $request->boolean('view_only', false);

            if ($viewOnly) {
                // Return existing recovery codes
                $recoveryCodes = $user->getTwoFactorRecoveryCodes();
                $message = 'Recovery codes loaded successfully.';
            } else {
                // Generate new recovery codes
                $recoveryCodes = $twoFactorService->regenerateRecoveryCodes($user);
                $message = 'Recovery codes have been regenerated successfully.';

                Log::info('Recovery codes regenerated for user', [
                    'user_id' => $user->id,
                    'admin_id' => Auth::id()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'recovery_codes' => $recoveryCodes
            ]);
        } catch (\Exception $e) {
            Log::error('Error regenerating recovery codes for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate recovery codes.'
            ], 500);
        }
    }

    /**
     * Get login history for a user.
     */
    public function loginHistory(Request $request, User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Check if current user can view this user
        if (!$user->canBeSeenBy($currentUser)) {
            abort(403, 'You do not have permission to view this user.');
        }

        if ($request->ajax()) {
            $loginLogs = $user->loginLogs()
                ->select(['id', 'login_at', 'status', 'ip_address', 'user_agent', 'location', 'created_at'])
                ->orderBy('login_at', 'desc');

            return DataTables::of($loginLogs)
                ->editColumn('login_at', function ($log) {
                    return formatUserDateTime($log->login_at);
                })
                ->editColumn('status', function ($log) {
                    $statusColors = [
                        'success' => 'bg-success',
                        'failed' => 'bg-danger',
                        'blocked' => 'bg-warning'
                    ];
                    $color = $statusColors[$log->status] ?? 'bg-secondary';
                    return '<span class="badge ' . $color . '">' . ucfirst($log->status) . '</span>';
                })
                ->editColumn('user_agent', function ($log) {
                    return '<span class="text-truncate d-inline-block" style="max-width: 200px;" title="' . htmlspecialchars($log->user_agent) . '">' .
                        htmlspecialchars($log->user_agent) . '</span>';
                })
                ->editColumn('location', function ($log) {
                    return $log->location ?: '<span class="text-muted">Unknown</span>';
                })
                ->rawColumns(['status', 'user_agent', 'location'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Get email history for a user.
     */
    public function emailHistory(Request $request, User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Check if current user can view this user
        if (!$user->canBeSeenBy($currentUser)) {
            abort(403, 'You do not have permission to view this user.');
        }

        // Check permission for email history
        if (!isSuperAdmin() && !hasPermission('admin.users.email-history')) {
            abort(403, 'You do not have permission to view email history.');
        }

        if ($request->ajax()) {
            $emailLogs = $user->emailLogs()
                ->select(['id', 'sent_at', 'subject', 'type', 'status', 'recipient_email', 'recipient_name', 'opened_at', 'clicked_at', 'created_at'])
                ->orderBy('sent_at', 'desc');

            return DataTables::of($emailLogs)
                ->editColumn('sent_at', function ($log) {
                    return formatUserDateTime($log->sent_at);
                })
                ->editColumn('subject', function ($log) {
                    return '<span class="text-truncate d-inline-block" style="max-width: 200px;" title="' . htmlspecialchars($log->subject) . '">' .
                        htmlspecialchars($log->subject) . '</span>';
                })
                ->editColumn('type', function ($log) {
                    $typeColors = [
                        'welcome' => 'bg-success',
                        'verification' => 'bg-info',
                        'password_reset' => 'bg-warning',
                        'notification' => 'bg-primary',
                        'marketing' => 'bg-secondary'
                    ];
                    $color = $typeColors[$log->type] ?? 'bg-secondary';
                    return '<span class="badge ' . $color . '">' . ucfirst(str_replace('_', ' ', $log->type)) . '</span>';
                })
                ->editColumn('status', function ($log) {
                    $statusColors = [
                        'sent' => 'bg-success',
                        'delivered' => 'bg-info',
                        'failed' => 'bg-danger',
                        'bounced' => 'bg-warning'
                    ];
                    $color = $statusColors[$log->status] ?? 'bg-secondary';
                    return '<span class="badge ' . $color . '">' . ucfirst($log->status) . '</span>';
                })
                ->editColumn('recipient_email', function ($log) {
                    $recipient = $log->recipient_email;
                    if ($log->recipient_name) {
                        $recipient = $log->recipient_name . ' <' . $log->recipient_email . '>';
                    }
                    return '<span class="text-truncate d-inline-block" style="max-width: 200px;" title="' . htmlspecialchars($recipient) . '">' .
                        htmlspecialchars($recipient) . '</span>';
                })
                ->addColumn('actions', function ($log) {
                    $actions = '<div class="d-flex gap-1">';
                    if ($log->opened_at) {
                        $actions .= '<span class="badge bg-success" title="Opened on ' . formatUserDateTime($log->opened_at) . '">
                                        <i data-lucide="eye" class="icon-xs"></i>
                                    </span>';
                    }
                    if ($log->clicked_at) {
                        $actions .= '<span class="badge bg-info" title="Clicked on ' . formatUserDateTime($log->clicked_at) . '">
                                        <i data-lucide="mouse-pointer" class="icon-xs"></i>
                                    </span>';
                    }
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['subject', 'type', 'status', 'recipient_email', 'actions'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Toggle user status (activate/deactivate).
     */
    public function toggleStatus(Request $request, User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Check if current user can manage this user
        if (!$user->canBeManagedBy($currentUser)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage this user.'
            ], 403);
        }

        // Prevent self-deactivation
        if ($user->id === $currentUser->id && $request->action === 'deactivate') {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account.'
            ], 403);
        }

        $action = $request->input('action');
        $newStatus = $action === 'activate';

        $user->update(['is_active' => $newStatus]);

        $message = $newStatus ? 'User activated successfully.' : 'User deactivated successfully.';

        Log::info('User status changed', [
            'user_id' => $user->id,
            'action' => $action,
            'new_status' => $newStatus,
            'admin_id' => $currentUser->id
        ]);

        // Send notification to admins
        $status = $newStatus ? 'activated' : 'deactivated';
        $this->notificationService->sendToAdmins(
            'user_status_toggled',
            'User Status Changed',
            "User '{$user->name}' has been {$status} by {$currentUser->name}",
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => $action,
                'new_status' => $newStatus,
                'changed_by' => $currentUser->name,
                'url' => route('admin.users.show', $user->id)
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Unlock a locked user account.
     */
    public function unlockAccount(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();

        // Only super admin can unlock accounts
        if (!$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only super administrators can unlock accounts.'
            ], 403);
        }

        // Check if user can manage this account
        if (!$user->canBeManagedBy($currentUser)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to unlock this account.'
            ], 403);
        }

        // Check if account is actually locked
        if (!$user->locked_until || now()->isAfter($user->locked_until)) {
            return response()->json([
                'success' => false,
                'message' => 'This account is not currently locked.'
            ], 400);
        }

        // Unlock the account
        $user->update([
            'locked_until' => null,
            'login_attempts' => 0
        ]);

        // Send notification to admins
        $this->notificationService->sendToAdmins(
            'user_account_unlocked',
            'User Account Unlocked',
            "User '{$user->name}' account has been unlocked by {$currentUser->name}",
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'unlocked_by' => $currentUser->name,
                'url' => route('admin.users.show', $user->id)
            ]
        );

        // Send notification to the user
        $this->notificationService->sendToUser(
            $user,
            'account_unlocked',
            'Your Account Has Been Unlocked',
            "Your account has been unlocked by an administrator. You can now login to your account.",
            [
                'unlocked_by' => $currentUser->name,
                'unlocked_at' => now()->toDateTimeString(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Account unlocked successfully. {$user->name} can now login."
        ]);
    }
}

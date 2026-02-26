<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseApiController
{
    protected string $version = 'v1';

    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        // Check permission
        if (!$request->user()->hasPermission('users.view')) {
            return $this->forbiddenResponse('You do not have permission to view users');
        }

        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = User::with('role');

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($perPage);

        // Transform users
        $users->getCollection()->transform(function ($user) {
            return $this->transformUser($user);
        });

        return $this->paginatedResponse($users, 'Users retrieved successfully');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        // Check permission
        if (!$request->user()->hasPermission('users.create')) {
            return $this->forbiddenResponse('You do not have permission to create users');
        }

        $minPasswordLength = password_min_length();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'min:' . $minPasswordLength],
            'role_id' => 'nullable|exists:roles,id',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'is_active' => $request->input('is_active', true),
        ]);

        return $this->createdResponse(
            $this->transformUser($user->load('role')),
            'User created successfully'
        );
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, User $user): JsonResponse
    {
        // Check permission (allow viewing own profile)
        if ($request->user()->id !== $user->id && !$request->user()->hasPermission('users.view')) {
            return $this->forbiddenResponse('You do not have permission to view this user');
        }

        return $this->successResponse(
            $this->transformUser($user->load('role')),
            'User retrieved successfully'
        );
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Check permission (allow updating own profile)
        if ($request->user()->id !== $user->id && !$request->user()->hasPermission('users.edit')) {
            return $this->forbiddenResponse('You do not have permission to update this user');
        }

        $minPasswordLength = password_min_length();

        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ];

        // Only admins can change role and status
        if ($request->user()->hasPermission('users.edit')) {
            $rules['role_id'] = 'sometimes|nullable|exists:roles,id';
            $rules['is_active'] = 'sometimes|boolean';
        }

        // Password update
        if ($request->has('password')) {
            $rules['password'] = ['required', 'string', 'min:' . $minPasswordLength];
            $rules['current_password'] = 'required_if:id,' . $request->user()->id;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Verify current password if updating own password
        if ($request->has('password') && $request->user()->id === $user->id) {
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->validationErrorResponse(['current_password' => ['Current password is incorrect']]);
            }
        }

        // Update fields
        $updateData = $request->only(['name', 'email']);
        
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->user()->hasPermission('users.edit')) {
            if ($request->has('role_id')) {
                $updateData['role_id'] = $request->role_id;
            }
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->is_active;
            }
        }

        $user->update($updateData);

        return $this->successResponse(
            $this->transformUser($user->fresh()->load('role')),
            'User updated successfully'
        );
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        // Check permission
        if (!$request->user()->hasPermission('users.delete')) {
            return $this->forbiddenResponse('You do not have permission to delete users');
        }

        // Prevent self-deletion
        if ($request->user()->id === $user->id) {
            return $this->errorResponse('You cannot delete your own account', 400);
        }

        // Prevent deleting super admin
        if ($user->isSuperAdmin()) {
            return $this->errorResponse('Cannot delete super admin account', 400);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }

    /**
     * Get user profile
     */
    public function profile(Request $request, User $user): JsonResponse
    {
        // Check permission (allow viewing own profile)
        if ($request->user()->id !== $user->id && !$request->user()->hasPermission('users.view')) {
            return $this->forbiddenResponse('You do not have permission to view this profile');
        }

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar_url,
            'role' => $user->role?->name,
            'email_verified' => $user->hasVerifiedEmail(),
            'two_factor_enabled' => $user->two_factor_enabled,
            'timezone' => $user->timezone,
            'created_at' => $user->created_at->toIso8601String(),
            'last_login_at' => $user->last_login_at?->toIso8601String(),
        ], 'User profile retrieved');
    }

    /**
     * Transform user for API response
     */
    protected function transformUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar_url,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'name' => $user->role->name,
            ] : null,
            'is_active' => $user->is_active,
            'email_verified' => $user->hasVerifiedEmail(),
            'created_at' => $user->created_at->toIso8601String(),
            'updated_at' => $user->updated_at->toIso8601String(),
        ];
    }
}


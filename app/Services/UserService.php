<?php

namespace App\Services;

use App\Models\User;
use App\Enums\UserGroupEnum;
use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * [Create] user record and attach default groups to support [System Enrollment]
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Determine user_role_id
            // If groups is provided and contains the Office Admins group, role is Admin, else Staff
            $isAdmin = isset($data['groups']) && in_array(UserGroupEnum::OFFICE_ADMINS->value, $data['groups']);
            $data['user_role_id'] = $isAdmin ? UserRoleEnum::ADMIN->value : UserRoleEnum::STAFF->value;

            // Hash the password securely
            $data['password'] = Hash::make($data['password']);

            // Create the user
            $user = User::create($data);

            // Sync groups if provided
            if (isset($data['groups'])) {
                $user->groups()->sync($data['groups']);
            }

            // Reload user with groups to return full data
            return $user->load('groups', 'role', 'office');
        });
    }

    /**
     * [Update] user record and sync groups to support [Profile Management]
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // Determine user_role_id based on updated groups
            if (isset($data['groups'])) {
                $isAdmin = in_array(UserGroupEnum::OFFICE_ADMINS->value, $data['groups']);
                $data['user_role_id'] = $isAdmin ? UserRoleEnum::ADMIN->value : UserRoleEnum::STAFF->value;
            }

            // Update user details
            $user->update($data);

            // Sync new groups
            if (isset($data['groups'])) {
                $user->groups()->sync($data['groups']);
            }

            // Reload user relations
            return $user->fresh('groups', 'role', 'office');
        });
    }

    /**
     * [Read] paginated user records with search filter to support [User Directory]
     */
    public function getUsers(array $filters)
    {
        $query = User::with(['role', 'office', 'groups']);

        // Apply search filter if provided
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('username', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'id';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $query->orderBy($sortBy, $sortDir);

        // Apply pagination (default 10, max 100)
        $perPage = $filters['per_page'] ?? 10;
        $perPage = min(max((int)$perPage, 1), 100);

        return $query->paginate($perPage);
    }

    /**
     * [Status Toggle] update the is_active flag for a single user
     */
    public function changeUserStatus(User $user, string $status): User
    {
        $user->update([
            'is_active' => ($status === 'active')
        ]);

        return $user->fresh('groups', 'role', 'office');
    }

    /**
     * [Delete] soft delete a single user record
     */
    public function deleteUser(User $user): void
    {
        // Leveraging Laravel's native soft deletes since we added the trait to migration
        $user->delete();
    }

    /**
     * [Bulk Delete] soft delete multiple user records
     */
    public function bulkDeleteUsers(array $userIds): void
    {
        User::whereIn('id', $userIds)->delete();
    }

    /**
     * [Bulk Activate] mark multiple users as active
     */
    public function bulkActivateUsers(array $userIds): void
    {
        User::whereIn('id', $userIds)->update(['is_active' => true]);
    }

    /**
     * [Bulk Deactivate] mark multiple users as inactive
     */
    public function bulkDeactivateUsers(array $userIds): void
    {
        User::whereIn('id', $userIds)->update(['is_active' => false]);
    }
}

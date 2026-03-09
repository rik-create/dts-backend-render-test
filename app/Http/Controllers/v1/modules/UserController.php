<?php

namespace App\Http\Controllers\v1\modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Users\StoreUserRequest;
use App\Http\Requests\V1\Users\UpdateUserRequest;
use App\Http\Requests\V1\Users\ChangeUserStatusRequest;
use App\Http\Requests\V1\Users\DeleteUserRequest;
use App\Http\Requests\V1\Users\BulkDeleteUserRequest;
use App\Http\Requests\V1\Users\BulkActivateUserRequest;
use App\Http\Requests\V1\Users\BulkDeactivateUserRequest;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * [Index] retrieves paginated users based on search criteria to support [Data Tables]
     */
    public function index(\App\Http\Requests\V1\Users\IndexUserRequest $request)
    {
        try {
            $users = $this->userService->getUsers($request->validated());
            return $this->handleResponse(true, 'Users retrieved successfully.', $users, [], 200);
        } catch (\Exception $e) {
            Log::error("User retrieval failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to retrieve users.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * [Store] new user payload to support [System Enrollment]
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return $this->handleResponse(true, 'User successfully created.', $user, [], 201);
        } catch (\Exception $e) {
            Log::error("User creation failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to create user.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * [Update] an existing user and sync to support [Profile Management]
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $updatedUser = $this->userService->updateUser($user, $request->validated());

            return $this->handleResponse(true, 'User successfully updated.', $updatedUser, [], 200);
        } catch (\Exception $e) {
            Log::error("User update failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to update user.', null, [$e->getMessage()], 500);
        }
    }
    /**
     * [Show] retrieve a single user profile to support [Profile Management]
     */
    public function show(User $user)
    {
        try {
            $user->load('groups', 'role', 'office');
            return $this->handleResponse(true, 'User retrieved successfully.', $user, [], 200);
        } catch (\Exception $e) {
            Log::error("User retrieval failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to retrieve user.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * [Destroy] delete a single user
     */
    public function destroy(DeleteUserRequest $request, User $user)
    {
        try {
            $currentUserId = $request->attributes->get('user_id');
            if ($user->id == $currentUserId) {
                return $this->handleResponse(false, 'You cannot delete your own account.', null, [], 403);
            }

            $this->userService->deleteUser($user);
            return $this->handleResponse(true, 'User successfully deleted.', null, [], 200);
        } catch (\Exception $e) {
            Log::error("User deletion failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to delete user.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * [Change Status] update a single user's status
     */
    public function changeUserStatus(ChangeUserStatusRequest $request, User $user)
    {
        try {
            $currentUserId = $request->attributes->get('user_id');
            if ($request->input('status') === 'inactive' && $user->id == $currentUserId) {
                return $this->handleResponse(false, 'You cannot deactivate your own account.', null, [], 403);
            }

            // Service updates boolean column 'is_active' based on status string
            $updatedUser = $this->userService->changeUserStatus($user, $request->input('status'));

            return $this->handleResponse(true, "User successfully marked as {$request->input('status')}.", $updatedUser, [], 200);
        } catch (\Exception $e) {
            Log::error("User status change failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to change user status.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * [Activate] bulk activate multiple users
     */
    public function activateUsers(BulkActivateUserRequest $request)
    {
        try {
            $this->userService->bulkActivateUsers($request->input('user_ids'));
            return $this->handleResponse(true, 'Users successfully activated.', null, [], 200);
        } catch (\Exception $e) {
            Log::error("Bulk user activation failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to activate users.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * [Deactivate] bulk deactivate multiple users
     */
    public function deactivateUsers(BulkDeactivateUserRequest $request)
    {
        try {
            $currentUserId = $request->attributes->get('user_id');
            $userIds = $request->input('user_ids');

            if (in_array($currentUserId, $userIds)) {
                return $this->handleResponse(false, 'Your list contains your own ID. You cannot deactivate your own account in bulk.', null, [], 403);
            }

            $this->userService->bulkDeactivateUsers($userIds);
            return $this->handleResponse(true, 'Users successfully deactivated.', null, [], 200);
        } catch (\Exception $e) {
            Log::error("Bulk user deactivation failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to deactivate users.', null, [$e->getMessage()], 500);
        }
    }

    /**
     * [Delete] bulk delete multiple users
     */
    public function deleteUsers(BulkDeleteUserRequest $request)
    {
        try {
            $currentUserId = $request->attributes->get('user_id');
            $userIds = $request->input('user_ids');

            if (in_array($currentUserId, $userIds)) {
                return $this->handleResponse(false, 'Your list contains your own ID. You cannot delete your own account in bulk.', null, [], 403);
            }

            $this->userService->bulkDeleteUsers($userIds);
            return $this->handleResponse(true, 'Users successfully deleted in bulk.', null, [], 200);
        } catch (\Exception $e) {
            Log::error("Bulk user deletion failed: " . $e->getMessage());
            return $this->handleResponse(false, 'Failed to delete users in bulk.', null, [$e->getMessage()], 500);
        }
    }
}

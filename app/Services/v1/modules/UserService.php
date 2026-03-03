<?php

namespace App\Services\v1\modules;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{
    /**
     * Create a new UserService instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Constructor logic if needed
    }

    /**
     * Example method for the service.
     * Replace this with your actual business logic.
     *
     * @param array $data
     * @return mixed
     */
    public function createUserService($request)
    {
        // TODO: Verify if this copied code works in this project.

        // Same as what GSO did
        // Generate password using last name (lowercase), date, and user role
        $date = date('mdY'); // Current date format: MMDDYYYY
        $lastNameFormatted = ucfirst(strtolower(preg_replace('/\s+/', '', $request['last_name']))); // Capitalize first letter & remove spaces

        $generatedPassword = "{$lastNameFormatted}{$date}";


        $createdUser = User::create([
           'user_role_id' => $request['user_role_id'] ?? 1, // Fallback to 1 if not provided, assuming role 1 exists
           'office_id' => $request['office_id'] ?? null,
           'first_name' => $request['first_name'],
           'last_name' => $request['last_name'],
           'middle_name' => $request['middle_name'] ?? null,
           'display_name' => $request['display_name'] ?? null,
           'email' => $request['email'],
           'username' => $request['username'] ?? null,
           'contact_number' => $request['contact_number'] ?? null,
           'is_active' => $request['is_active'] ?? true,
           'password' => Hash::make($generatedPassword),
        ]);
        return [
            'success' => true,
            'message' => 'User created successfully',
        ];
    }


    public function updateUserService(array $data, $id)
    {
        // TODO: Verify if this copied code works in this project.
        $user = User::findOrFail($id);

        $user->fill($data);

        if (!$user->isDirty()) {
            return [
                'success' => true,
                'message' => 'No changes were made to the user profile.',
            ];
        }

        $changedFields = $user->getDirty();
        $user->save();

        return [
            'success' => true,
            'message' => 'User profile updated successfully.',
        ];
    }

    public function deleteUserService($id) {
        // TODO: Verify if this copied code works in this project.

        if($id == 1){
            return [
                'success' => false,
                'message' => 'User cannot be deleted.',
            ];
        }

        $user = User::withTrashed()->findOrFail($id);

        if(!$user){
            throw new ModelNotFoundException();
        }

        if ($user->trashed()) {
            throw new \Exception("User has already been deleted.");
        }

        // this is also the same in GSO adding Archived_time_ to email
        $user->email = "Archived_" . time() . "_" . $user->email;
        $user->save();

        $user->delete();

        return [
            'success' => true,
            'message' => 'User deleted successfully.',
        ];
    }

}

<?php

namespace App\Http\Controllers\v1\modules;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenericResponseResource;
use App\Services\v1\modules\UserService;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service) {
        $this->service = $service;
    }

    /**
     * Create a user
     *
     * This eneble authenticated and authorize to create a user
     *
    */
    public function createUser(UserRequest $request) {
        try{

            $response = (object) $this->service->createUserService($request->validated());

            return new GenericResponseResource($response);

        }catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
                ], 500);
        }

    }


    /**
     * Update a user
     *
     * This eneble authenticated and authorize to update a user
     *
    */
     public function updateUser(UserRequest $request, $id) {
        try{

            $response = (object) $this->service->updateUserService($request->validated(), $id);

            return new GenericResponseResource($response);

        }catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);

        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }

    /**
     * Delete a user
     *
     * This eneble authenticated and authorize user to be deleted
     *
    */
    public function deleteUser($id) {
        try{

            $response = (object) $this->service->deleteUserService($id);

            return new GenericResponseResource($response);

        }catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);

        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }
}

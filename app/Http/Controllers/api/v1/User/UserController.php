<?php

namespace App\Http\Controllers\api\v1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\User\UserRequest;
use App\Http\Resources\api\v1\User\UserResource;
use App\Http\Resources\api\v1\User\UserResourceCollection;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * @group User Management
 *
 * Handles all user-related API operations.
 */
class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $users = User::all();
            return (new UserResourceCollection($users))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to fetch users: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve users.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function show(Request $request, User $user): JsonResponse
    {
        try {
            return (new UserResource($user))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to fetch user: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created user.
     *
     * @param UserRequest $request
     * @return JsonResponse
     */
    public function store(UserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }

            $user = User::create($data);

            return (new UserResource($user))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to create user: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to create user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified user.
     *
     * @param UserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        try {
            $data = $request->validated();

            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }

            $user->update($data);

            return (new UserResource($user))->toResponse($request);
        } catch (Throwable $e) {
            Log::error('Failed to update user: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to update user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        try {
            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully.',
            ], 200);
        } catch (Throwable $e) {
            Log::error('Failed to delete user: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to delete user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

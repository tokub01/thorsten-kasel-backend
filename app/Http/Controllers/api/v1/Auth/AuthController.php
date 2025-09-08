<?php

namespace App\Http\Controllers\api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\Auth\LogoutRequest;
use App\Http\Responses\api\v1\Auth\LogoutResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\api\v1\Auth\LoginRequest;
use App\Http\Responses\api\v1\Auth\LoginResource;
use App\Http\Requests\api\v1\Auth\RegisterRequest;
use App\Http\Responses\api\v1\Auth\RegisterResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handles a login request and returns a Sanctum token along with user information.
     *
     * Authenticates the user and returns an access token for API usage.
     *
     * @group Authentication
     *
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam password string required The user's password. Example: password
     *
     * @url POST /api/auth/login
     *
     * @param \App\Http\Requests\LoginRequest $request The validated login request.
     * @return \Illuminate\Http\JsonResponse JSON response containing success status, user data, and API token.
     *
     * @throws \Illuminate\Validation\ValidationException If the input validation fails.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Login successful.",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "role": "admin"
     *   },
     *   "token": "1|abc123xyz..."
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "Invalid login credentials."
     * }
     */
    public function login(LoginRequest $request) : JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Die eingegebenen Zugangsdaten sind ungültig.',
            ], 401);
        }

        return new JsonResponse(new LoginResource);
    }

    /**
     * Registers a new user and issues a Sanctum token for API authentication.
     *
     * @group Authentication
     *
     * @bodyParam name string required The full name of the user. Example: John Doe
     * @bodyParam email string required The email address of the user. Example: john@example.com
     * @bodyParam password string required The password for the account. Example: password123
     * @bodyParam password_confirmation string required Confirmation of the password. Example: password123
     *
     * @url POST /api/auth/register
     *
     * @param RegisterRequest $request Validated registration data.
     * @return \Illuminate\Http\JsonResponse JSON response with user data and access token.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Registration successful.",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com"
     *   },
     *   "token": "1|abc123xyz..."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": [
     *       "The email has already been taken."
     *     ]
     *   }
     * }
     */
    public function register(RegisterRequest $request) : JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return new JsonResponse(new RegisterResource($user, $token));
    }

    /**
     * Logs out the authenticated user by deleting their current API token.
     *
     * @group Authentication
     *
     * @url POST /api/auth/logout
     *
     * @param LogoutRequest $request
     * @return \Illuminate\Http\JsonResponse JSON success message on logout.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Erfolgreich ausgeloggt."
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "Nicht authentifiziert."
     * }
     */
    public function logout(LogoutRequest $request) : JsonResponse
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return new JsonResponse(new LogoutResource);
    }

}

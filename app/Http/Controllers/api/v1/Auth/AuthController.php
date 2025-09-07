<?php

namespace App\Http\Controllers\api\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handles a login request and returns a Sanctum token along with user information.
     *
     * Authenticates the user and returns an access token for API usage.
     *
     * @group Authentication
     *
     * @bodyParam email string required The user's email. Example: user@example.com
     * @bodyParam password string required The user's password. Example: password
     *
     * @url POST /api/auth/login
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing login credentials.
     * @return \Illuminate\Http\JsonResponse JSON response with success status, user data, and API token.
     *
     * @throws \Illuminate\Validation\ValidationException If the validation of input data fails.
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
    public function login(Request $request) : JsonResponse {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Bitte geben Sie Ihre E-Mail Adresse ein.',
            'password.required' => 'Bitte geben Sie Ihr Passwort ein.',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Die eingegebenen Zugangsdaten sind ungültig.',
            ], 401);
        }

        $user = Auth::user();

        $user->tokens()->where('name', 'login_token')->delete();

        $token = $user->createToken('login_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Anmeldung erfolgreich.',
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'role'     => $user->role ?? null,
            ],
            'token' => $token,
        ]);
    }
}

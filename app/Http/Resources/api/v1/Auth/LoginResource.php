<?php

namespace App\Http\Responses\api\v1\Auth;

use Illuminate\Support\Facades\Response;

use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Auth;

use App\Models\User;

class LoginResource
{
    protected $user;

    protected $token;

    public function __construct()
    {
        $this->user = Auth::user();

        $this->user->tokens()->where('name', 'login_token')->delete();

        $this->token = $this->user->createToken('login_token')->plainTextToken;
    }

    public function toResponse($request) : JsonResponse
    {
        return Response::json([
            'success' => true,
            'message' => 'Anmeldung erfolgreich.',
            'user' => [
                'id'       => $this->user->id,
                'name'     => $this->user->name,
                'email'    => $this->user->email,
                'role'     => $this->user->role ?? null,
            ],
            'token' => $this->token,
        ]);
    }
}

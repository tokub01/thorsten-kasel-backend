<?php

namespace App\Http\Responses\api\v1\Auth;

use Illuminate\Contracts\Support\Responsable;

class RegisterResource implements Responsable
{
    protected $user;
    protected $token;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function toResponse($request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                // ggf. weitere User-Daten ohne sensible Infos
            ],
            'token' => $this->token,
        ], 201); // 201 Created
    }
}

<?php


namespace App\Http\Responses\api\v1\Auth;

use Illuminate\Contracts\Support\Responsable;

class LogoutResource implements Responsable
{
    public function toResponse($request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Erfolgreich ausgeloggt.',
        ]);
    }
}

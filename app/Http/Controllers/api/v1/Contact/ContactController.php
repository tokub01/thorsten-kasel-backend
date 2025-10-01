<?php

namespace App\Http\Controllers\api\v1\Contact;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\PendingContactRequest;
use App\Mail\ContactVerificationMail;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;
use App\Models\ContactRequest;
use App\Models\User;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'message'         => 'required|string',
            'recaptcha_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validierung fehlgeschlagen',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('recaptcha_token'),
            'remoteip' => $request->ip(),
        ]);

        $body = $response->json();

        if (!isset($body['success']) || $body['success'] !== true) {
            return response()->json([
                'success' => false,
                'message' => 'reCAPTCHA Validierung fehlgeschlagen',
                'errors'  => [
                    'recaptcha' => $body['error-codes'] ?? ['Ungültiger Token'],
                ],
            ], 422);
        }

        // Score prüfen (für reCAPTCHA v3)
        if (isset($body['score']) && $body['score'] < 0.5) {
            return response()->json([
                'success' => false,
                'message' => 'Du wurdest als Bot erkannt',
                'errors'  => [
                    'recaptcha' => ['Score zu niedrig (' . $body['score'] . ')'],
                ],
            ], 403);
        }

        $token = Str::random(32);

        PendingContactRequest::create([
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
            'token' => $token,
        ]);

        $url = route('contact.verify', ['token' => $token]);

        Mail::to($request->email)->send(new ContactVerificationMail($url));

        return response()->json([
            'success' => true,
            'message' => 'Nachricht erfolgreich gesendet!',
        ], 200);
    }

    public function verify(Request $request)
    {
        $token = $request['token'];

        $pending = PendingContactRequest::where('token', $token)->first();

        if (!$pending) {
            return response()->json([
                'success' => false,
                'message' => "Verifizierungs E-Mail ungültig!",
                'errors' => '',
            ], 422);
        }

        if ($pending->isVerified) {
            return response()->json([
                'success' => false,
                'message' => "E-Mail bereits erfolgreich verifiziert!",
                'errors' => '',
            ], 422);
        }

        $pending->update(['isVerified' => true]);

        ContactRequest::create([
            'email' => $pending->email,
            'name' => $pending->name,
            'message' => $pending->message,
        ]);

        Mail::to('tobias.kubina@protonmail.com')
            ->send(new ContactMail($pending->name, $pending->email, $pending->message));

        return response()->json([
            'success' => true,
            'message' => "Verifizierungsemail erfolgreich gesendet!",
            'errors' => '',
        ]);
    }
}

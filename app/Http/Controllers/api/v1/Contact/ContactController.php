<?php

namespace App\Http\Controllers\api\v1\Contact;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function getTest(Request $request){
        return "Hello";
    }

    public function submit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
            'recaptcha_token' => 'required|string',
        ]);

        // reCAPTCHA validieren
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('recaptcha_token'),
            'remoteip' => $request->ip(),
        ]);

        $body = $response->json();

        if (!isset($body['success']) || $body['success'] !== true) {
            return back()->withErrors(['recaptcha' => 'reCAPTCHA Validierung fehlgeschlagen']);
        }

        // Score prüfen (optional, v3)
        if (isset($body['score']) && $body['score'] < 0.5) {
            return back()->withErrors(['recaptcha' => 'Du wurdest als Bot erkannt']);
        }

        // Formular weiter verarbeiten
        // z.B. E-Mail senden oder in DB speichern
        return back()->with('success', 'Nachricht erfolgreich gesendet!');
    }
}

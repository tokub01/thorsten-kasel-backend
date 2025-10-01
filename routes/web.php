<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\Contact\ContactController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/contact/verify/{token}', [ContactController::class, 'verify'])
    ->name('contact.verify');
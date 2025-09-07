<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->namespace('App\Http\Controllers\api\v1\Auth')
    ->group(function () {
        Route::post('login', 'AuthController@login');
    });

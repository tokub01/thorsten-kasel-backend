<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - v1
|--------------------------------------------------------------------------
|
| API-Routen für Version 1
|
*/

Route::prefix('auth')
    ->namespace('App\Http\Controllers\api\v1\Auth')
    ->group(function () {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');
        Route::middleware('auth:sanctum')->post('logout', 'AuthController@logout');
    });

Route::prefix('products')
    ->namespace('App\Http\Controllers\api\v1\Product')
    ->group(function () {
        Route::get('/', 'ProductController@index');
        Route::get('{product}', 'ProductController@show');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', 'ProductController@store');
            Route::put('{product}', 'ProductController@update');
            Route::delete('{product}', 'ProductController@destroy');
        });
    });

Route::prefix('categories')
    ->namespace('App\Http\Controllers\api\v1\Category')
    ->group(function () {
        Route::get('/', 'CategoryController@index');
        Route::get('{category}', 'CategoryController@show');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', 'CategoryController@store');
            Route::put('{category}', 'CategoryController@update');
            Route::delete('{category}', 'CategoryController@destroy');
        });
    });

Route::prefix('news')
    ->namespace('App\Http\Controllers\api\v1\News')
    ->group(function () {
        Route::get('/', 'NewsController@index');
        Route::get('{news}', 'NewsController@show');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', 'NewsController@store');
            Route::put('{news}', 'NewsController@update');
            Route::delete('{news}', 'NewsController@destroy');
        });
    });

Route::prefix('exhibitions')
    ->namespace('App\Http\Controllers\api\v1\Exhibition')
    ->group(function () {
        Route::get('/', 'ExhibitionController@index');
        Route::get('{exhibition}', 'ExhibitionController@show');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', 'ExhibitionController@store');
            Route::put('{exhibition}', 'ExhibitionController@update');
            Route::delete('{exhibition}', 'ExhibitionController@destroy');
        });
    });

Route::prefix('contact')
    ->namespace('App\Http\Controllers\api\v1\Contact')
    ->group(function () {
        Route::post('/', 'ContactController@submit');
        Route::post('/verify', 'ContactController@verify');
    });
Route::prefix('contact-requests')
    ->namespace('App\Http\Controllers\api\v1\Contact')
    ->group(function () {
        Route::get('/', 'ContactController@getContactRequest');
        Route::put('{contactRequest}', 'ContactController@updateContactRequest');
    });

Route::prefix('users')
    ->namespace('App\Http\Controllers\api\v1\User')
    ->group(function () {
            Route::get('{user}/biography', 'UserController@biography');
            Route::middleware('auth:sanctum')->group(function () {
                Route::get('/', 'UserController@index');
                Route::get('{user}', 'UserController@show');
                Route::post('/', 'UserController@store');
                Route::put('{user}', 'UserController@update');
                Route::delete('/{user}', 'UserController@destroy');
            });
    });

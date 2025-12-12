<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/gemini-test', [\App\Http\Controllers\GeminiController::class, 'test']);

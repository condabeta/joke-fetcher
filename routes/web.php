<?php

use App\Models\Joke;
use Illuminate\Support\Facades\Route;

// Task 2: route returning DB rows as JSON.
Route::get('/jokes', function () {
    return response()->json(Joke::latest()->take(50)->get());
});

// Bonus: visitor statistics page (HTTP basic auth against users table).
Route::get('/stats', function () {
    return view('stats');
})->middleware('auth.basic');

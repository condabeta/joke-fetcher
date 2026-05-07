<?php

use App\Models\Joke;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/jokes', function () {
    return response()->json(Joke::latest()->take(50)->get());
});
<?php

use App\Http\Controllers\VisitorController;
use Illuminate\Support\Facades\Route;

Route::post('/track-visit', [VisitorController::class, 'track']);
Route::get('/stats/data', [VisitorController::class, 'data']);

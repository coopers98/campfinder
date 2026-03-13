<?php

use App\Http\Controllers\RecommendController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/recommend', RecommendController::class);

<?php

use App\Http\Controllers\DataViewController;
use App\Http\Controllers\RecommendController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/data', DataViewController::class);

Route::post('/api/recommend', RecommendController::class);

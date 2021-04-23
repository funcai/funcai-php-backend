<?php

use App\Http\Controllers\Api\ImageClassificationController;
use App\Http\Controllers\Api\ImageStylizationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:inference'])->group(function () {
    Route::post('/demo/image-classification', [ImageClassificationController::class, 'classify']);
    Route::post('/demo/image-stylization', [ImageStylizationController::class, 'stylize']);
});

Route::get('/demo/image-classification/status/{key}', [ImageClassificationController::class, 'status']);
Route::get('/demo/image-stylization/status/{key}', [ImageStylizationController::class, 'status']);

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SchoolController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('schools/search', [SchoolController::class, 'search'])
        ->name(name: 'schools.search');
    Route::resource('schools', SchoolController::class);
});
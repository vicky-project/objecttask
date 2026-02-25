<?php

use Illuminate\Support\Facades\Route;
use Modules\ObjectTask\Http\Controllers\ObjectTaskController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('objecttasks', ObjectTaskController::class)->names('objecttask');
});

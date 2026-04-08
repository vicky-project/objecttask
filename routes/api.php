<?php

use Illuminate\Support\Facades\Route;
use Modules\ObjectTask\Http\Controllers\ObjectTaskController;

Route::prefix("data-object")
->middleware('auth:sanctum')
->group(function () {
  Route::get('', [ObjectTaskController::class, 'index']);

  Route::prefix("categories")->group(function () {
    Route::get("", [ObjectTaskController::class, "categories"]);
    Route::get("/{id}/contents", [ObjectTaskController::class, "contents"]);
  });

  Route::get("task-codes", [ObjectTaskController::class, "taskCodes"]);
});
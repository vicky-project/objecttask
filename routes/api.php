<?php

use Illuminate\Support\Facades\Route;
use Modules\ObjectTask\Http\Controllers\ObjectTaskController;

Route::middleware(["auth:sanctum"])
	->prefix("v1")
	->group(function () {
		Route::apiResource("objecttasks", ObjectTaskController::class)->names(
			"objecttask",
		);
	});

Route::prefix("data-object")->group(function () {
	Route::prefix("categories")->group(function () {
		Route::get("", [ObjectTaskController::class, "categories"]);
		Route::get("/{id}/contents", [ObjectTaskController::class, "contents"]);
	});

	Route::get("task-codes", [ObjectTaskController::class, "taskCodes"]);
});

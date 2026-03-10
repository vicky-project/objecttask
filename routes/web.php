<?php

use Illuminate\Support\Facades\Route;
use Modules\ObjectTask\Http\Controllers\ObjectTaskController;

Route::prefix("apps")
->name("apps.")->middleware("telegram.miniapp")
->group(function () {
  Route::get("objecttask", [ObjectTaskController::class, "index"])->name(
    "objecttask",
  );
});
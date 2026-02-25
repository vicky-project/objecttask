<?php

use Illuminate\Support\Facades\Route;
use Modules\ObjectTask\Http\Controllers\ObjectTaskController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('objecttasks', ObjectTaskController::class)->names('objecttask');
});

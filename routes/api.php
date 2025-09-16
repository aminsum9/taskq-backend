<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::put('/',  [TaskController::class, 'createTask']);
Route::get('/',  [TaskController::class, 'getTask']);

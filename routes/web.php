<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\SpeedrunController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/speedruns', [SpeedrunController::class, 'index']);
Route::get('/speedruns/{speedrun}', [SpeedrunController::class, 'show']);
Route::get('/watch', [SpeedrunController::class, 'find']);
Route::get('/watch/{speedrun}', [SpeedrunController::class, 'show']);



Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/platforms/{platform}', [PlatformController::class, 'show']);
Route::get('/platforms', [PlatformController::class, 'index']);




Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

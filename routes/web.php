<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\RunnerController;
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

Route::middleware(['auth:sanctum', 'verified'])->group(function() {
    Route::middleware('ability:view_admin')->group(function()
    {
        Route::get('/admin', [AdminController::class, 'index']);
    });

    Route::get('/speedruns/new', [SpeedrunController::class, 'create']);
    Route::post('/speedruns/new', [SpeedrunController::class, 'store']);

    Route::put('/speedruns/{speedrun}', [SpeedrunController::class, 'update']);
    Route::delete('/speedruns/{speedrun}', [SpeedrunController::class, 'delete']);

});


Route::get('/', [SpeedrunController::class, 'welcome']);

Route::get('/runner/{user:name}', [RunnerController::class, 'show']);

Route::get('/speedruns', [SpeedrunController::class, 'index'])->name('speedruns');
Route::get('/speedruns/{speedrun}', [SpeedrunController::class, 'show']);
Route::get('/watch', [SpeedrunController::class, 'find']);
Route::get('/watch/{speedrun}', [SpeedrunController::class, 'show']);



Route::get('/categories/{category:title}', [CategoryController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index'])->name('categories');

Route::get('/platforms/{platform:title}', [PlatformController::class, 'show']);
Route::get('/platforms', [PlatformController::class, 'index'])->name('platforms');


Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

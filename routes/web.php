<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouncilController;
use App\Http\Controllers\DisqualificationController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\RunnerController;
use App\Http\Controllers\SpeedrunController;
use Illuminate\Http\Request;
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

    Route::get('/billing-portal', function (Request $request) {
        $request->user()->createOrGetStripeCustomer();
        return $request->user()->redirectToBillingPortal();
    });

    Route::get('/council/join', [CouncilController::class, 'join']);
    Route::post('/council/join', [CouncilController::class, 'store']);

    Route::delete('/council', [CouncilController::class, 'destroy'])
        ->name('council.destroy');

    Route::middleware('can:view_admin')->group(function()
    {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin');
        Route::patch('/speedruns/{speedrun}', [SpeedrunController::class, 'verify']);

        Route::middleware('can:manage_banners')->group(function() {

            Route::get('/banners', [BannerController::class, 'index']);
            Route::get('/banners/new', [BannerController::class, 'create']);
            Route::post('/banners/new', [BannerController::class, 'store']);

            Route::get('/banners/{banner}', [BannerController::class, 'edit']);
            Route::put('/banners/{banner}', [BannerController::class, 'update']);

            Route::delete('/banners/{banner}', [BannerController::class, 'destroy']);

        });

        Route::middleware('can:manage_speedruns')->group(function() {
            Route::get('/admin/verify', [AdminController::class, 'verify']);
            Route::get('/admin/disqualify/{speedrun}', [DisqualificationController::class, 'create'])
                ->name('disqualify.new');
            Route::post('/admin/disqualify/{speedrun}', [DisqualificationController::class, 'store'])
                ->name('disqualify.store');
            Route::get('/admin/disqualifications', [DisqualificationController::class, 'index']);
            Route::get('/admin/disqualifications/{disqualification}', [DisqualificationController::class, 'view']);
            Route::put('/admin/disqualifications/{disqualification}', [DisqualificationController::class, 'update']);
            Route::delete('/admin/disqualifications/{disqualification}', [DisqualificationController::class, 'destroy']);
        });

    });

    Route::get('/speedruns/new', [SpeedrunController::class, 'create'])->name('submit-speedrun');
    Route::post('/speedruns/new', [SpeedrunController::class, 'store']);

    Route::put('/speedruns/{speedrun}', [SpeedrunController::class, 'update']);
    Route::delete('/speedruns/{speedrun}', [SpeedrunController::class, 'delete']);

    Route::patch('/banners/{banner}', [BannerController::class, 'desync']);

});
// Route::get('/council/chat', \App\Http\Livewire\CouncilChatroom::class)->name('chat');
Route::get('/council', [CouncilController::class, 'index'])->name('council');

Route::get('/elections', [ElectionController::class, 'index'])->name('elections');
Route::get('/elections/{election}', [ElectionController::class, 'show']);

Route::get('/', [SpeedrunController::class, 'welcome'])->name('home');
Route::get('/dashboard', [SpeedrunController::class, 'welcome'])->name('dashboard');

Route::get('/runner/{user:name}', [RunnerController::class, 'show']);

Route::get('/speedruns', [SpeedrunController::class, 'index'])->name('speedruns');
Route::get('/speedruns/{speedrun}', [SpeedrunController::class, 'show']);
Route::get('/watch', [SpeedrunController::class, 'find']);
Route::get('/watch/{speedrun}', [SpeedrunController::class, 'show']);



Route::get('/categories/{category:name}', [CategoryController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index'])->name('categories');

Route::get('/platforms/{platform:name}', [PlatformController::class, 'show']);
Route::get('/platforms', [PlatformController::class, 'index'])->name('platforms');



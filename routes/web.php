<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

use PeterMarkley\Tollerus\Http\Controllers\HelloController;

$baseMiddleware = Config::get('tollerus.middleware', ['web']);
$adminMiddleware = collect(Config::get('tollerus.admin_middleware', []))
    ->diff($baseMiddleware)->unique()->values()->all();;

Route::prefix(Config::get('tollerus.route_prefix', 'tollerus'))
    ->as('tollerus.')
    ->middleware($baseMiddleware)
    ->group(function () use ($adminMiddleware) {
        Route::get('/', [HelloController::class, 'index'])->name('hello');

        // public/browse UI
        // Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // write/manage UI behind stronger middleware
        Route::middleware($adminMiddleware)
            ->group(function () {
                // Route::get('/entries/create', [EntriesController::class, 'create'])->name('entries.create');
                // Route::post('/entries', [EntriesController::class, 'store'])->name('entries.store');
                // …other edit/delete routes
            });
    });

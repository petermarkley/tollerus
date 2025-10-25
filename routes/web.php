<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

use PeterMarkley\Tollerus\Http\Controllers\HelloController;
use PeterMarkley\Tollerus\Http\Controllers\LanguageController;
use PeterMarkley\Tollerus\Http\Controllers\NeographyController;
use PeterMarkley\Tollerus\Livewire\LanguageEditor;

$baseMiddleware = Config::get('tollerus.middleware', ['web']);
$adminMiddleware = collect(Config::get('tollerus.admin_middleware', []))
    ->diff($baseMiddleware)->unique()->values()->all();;

Route::prefix(Config::get('tollerus.route_prefix', 'tollerus'))
    ->as('tollerus.')
    ->middleware($baseMiddleware)
    ->group(function () use ($adminMiddleware) {

        Route::get('/', [HelloController::class, 'index'])->name('hello');

        // Routes for the admin area of the app
        Route::prefix('admin')
            ->as('admin.')
            ->middleware($adminMiddleware)
            ->group(function () {
                Route::get('/languages', [LanguageController::class, 'index'])->name('languages.index');
                Route::get('/languages/{language}', LanguageEditor::class)->name('languages.edit');
                Route::get('/neographies', [NeographyController::class, 'index'])->name('neographies.index');
            });
    });

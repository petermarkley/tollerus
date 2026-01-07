<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

use PeterMarkley\Tollerus\Http\Controllers\AdminController;
use PeterMarkley\Tollerus\Http\Controllers\LanguageController;
use PeterMarkley\Tollerus\Http\Controllers\NeographyController;
use PeterMarkley\Tollerus\Livewire\AutoInflectionEditor;
use PeterMarkley\Tollerus\Livewire\InflectionTableEditor;
use PeterMarkley\Tollerus\Livewire\LanguageEditor;
use PeterMarkley\Tollerus\Livewire\NeographyEditor;
use PeterMarkley\Tollerus\Livewire\NeographySectionEditor;

$baseMiddleware = Config::get('tollerus.middleware', ['web']);
$adminMiddleware = collect(Config::get('tollerus.admin_middleware', []))
    ->diff($baseMiddleware)->unique()->values()->all();;

Route::prefix(Config::get('tollerus.route_prefix', 'tollerus'))
    ->as('tollerus.')
    ->middleware($baseMiddleware)
    ->group(function () use ($adminMiddleware) {

        Route::get('/', fn () => redirect(route('tollerus.admin.index')));

        // Routes for the admin area of the app
        Route::prefix('admin')
            ->as('admin.')
            ->middleware($adminMiddleware)
            ->group(function () {
                Route::get('/', [AdminController::class, 'index'])->name('index');
                Route::prefix('languages')
                    ->as('languages.')
                    ->group(function () {
                        Route::get('/', [LanguageController::class, 'index'])->name('index');
                        Route::post('/', [LanguageController::class, 'store'])->name('store');
                        Route::prefix('{language}')->group(function () {
                            Route::delete('/', [LanguageController::class, 'destroy'])->name('destroy');
                            Route::get('/', LanguageEditor::class)->name('edit');
                            Route::get('/{tab}', LanguageEditor::class)
                                ->whereIn('tab', ['neographies', 'grammar', 'entries'])
                                ->name('edit.tab');
                            Route::prefix('grammar/{group}')->group(function () {
                                Route::get('/inflection-tables', InflectionTableEditor::class)->name('inflection-tables');
                                Route::get('/inflection-rows/{row}/auto', AutoInflectionEditor::class)->name('auto-inflection');
                            });
                            Route::prefix('entries/{entry}')
                                ->as('entry.')
                                ->group(function () {
                                    Route::get('/', fn () => 'fixme')->name('edit');
                                });
                        });
                    });
                Route::prefix('neographies')
                    ->as('neographies.')
                    ->group(function () {
                        Route::get('/', [NeographyController::class, 'index'])->name('index');
                        Route::post('/', [NeographyController::class, 'store'])->name('store');
                        Route::prefix('/{neography}')->group(function () {
                            Route::delete('/', [NeographyController::class, 'destroy'])->name('destroy');
                            Route::get('/', NeographyEditor::class)->name('edit');
                            Route::get('/{tab}', NeographyEditor::class)
                                ->whereIn('tab', ['font', 'glyphs', 'keyboards'])
                                ->name('edit.tab');
                            Route::prefix('glyphs/{sect}')
                                ->as('glyphs.')
                                ->group(function () {
                                    Route::get('/', NeographySectionEditor::class)->name('edit');
                                });
                        });
                    });
            });
    });

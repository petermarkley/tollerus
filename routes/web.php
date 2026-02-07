<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

use PeterMarkley\Tollerus\Http\Controllers\AdminController;
use PeterMarkley\Tollerus\Http\Controllers\EntryController;
use PeterMarkley\Tollerus\Http\Controllers\LanguageController;
use PeterMarkley\Tollerus\Http\Controllers\NeographyController;
use PeterMarkley\Tollerus\Http\Controllers\PublicLanguageController;
use PeterMarkley\Tollerus\Livewire\AutoInflectionEditor;
use PeterMarkley\Tollerus\Livewire\EntryEditor;
use PeterMarkley\Tollerus\Livewire\InflectionTableEditor;
use PeterMarkley\Tollerus\Livewire\LanguageEditor;
use PeterMarkley\Tollerus\Livewire\NeographyEditor;
use PeterMarkley\Tollerus\Livewire\NeographySectionEditor;
use PeterMarkley\Tollerus\Livewire\PublicWordLookup;

$baseMiddleware = Config::get('tollerus.middleware', ['web']);
$adminMiddleware = collect(Config::get('tollerus.admin_middleware', []))
    ->diff($baseMiddleware)->unique()->values()->all();;

Route::as('tollerus.')
    ->middleware($baseMiddleware)
    ->group(function () use ($adminMiddleware) {

        // Routes for the public area of the app
        Route::prefix(Config::get('tollerus.public_route_prefix', 'tollerus'))
            ->as('public.')
            ->group(function () {
                Route::get('/', PublicWordLookup::class)->name('index');
                Route::prefix('languages')
                    ->as('languages.')
                    ->group(function () {
                        Route::get('/', [PublicLanguageController::class, 'index'])->name('index');
                    });
            });

        // Routes for the admin area of the app
        Route::prefix(Config::get('tollerus.admin_route_prefix', 'tollerus/admin'))
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
                            Route::prefix('grammar/{wordClassGroup}')->group(function () {
                                Route::get('/inflection-tables', InflectionTableEditor::class)
                                    ->scopeBindings()
                                    ->name('inflection-tables');
                                /**
                                 * We can't use `->scopeBindings()` here. See comment in
                                 * Livewire class `mount()` method, where we manually
                                 * validate the model bindings.
                                 */
                                Route::get('/inflection-rows/{row}/auto', AutoInflectionEditor::class)->name('auto-inflection');
                            });
                            Route::prefix('entries')
                                ->as('entries.')
                                ->group(function () {
                                    Route::post('/', [EntryController::class, 'store'])->name('store');
                                    Route::prefix('{entry}')->scopeBindings()->group(function () {
                                        Route::delete('/', [EntryController::class, 'destroy'])->name('destroy');
                                        Route::get('/', EntryEditor::class)->name('edit');
                                    });
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
                            Route::prefix('glyphs/{section}')->scopeBindings()
                                ->as('glyphs.')
                                ->group(function () {
                                    Route::get('/', NeographySectionEditor::class)->name('edit');
                                });
                        });
                    });
            });
    });

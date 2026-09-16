<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! \Illuminate\Support\Facades\Route::has('projects.index') && class_exists(\App\Http\Controllers\ProjectController::class)) {
            \Illuminate\Support\Facades\Route::middleware(['web', 'auth'])->group(function () {
                \Illuminate\Support\Facades\Route::get('/projects', [\App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
                \Illuminate\Support\Facades\Route::post('/projects', [\App\Http\Controllers\ProjectController::class, 'store'])->name('projects.store');
                \Illuminate\Support\Facades\Route::put('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'update'])->name('projects.update');
                \Illuminate\Support\Facades\Route::delete('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'destroy'])->name('projects.destroy');
            });
        }
    }
}

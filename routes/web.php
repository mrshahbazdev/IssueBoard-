<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\InvitationAcceptanceController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use Illuminate\Support\Facades\Route;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('guest')->group(function () {
    Route::get('/invite/{token}', [InvitationAcceptanceController::class, 'create'])
        ->name('invitations.accept');
    Route::post('/invite/{token}', [InvitationAcceptanceController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('invitations.store');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
    Route::get('/two-factor-challenge', [\App\Http\Controllers\Auth\TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [\App\Http\Controllers\Auth\TwoFactorChallengeController::class, 'store']);
    Route::post('/two-factor-challenge/resend', [\App\Http\Controllers\Auth\TwoFactorChallengeController::class, 'resend'])->name('two-factor.resend');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('issueboard.index'))->name('home');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/two-factor', [ProfileController::class, 'toggleTwoFactor'])->name('profile.two-factor.toggle');
    Route::post('/profile/workspace', [ProfileController::class, 'updateWorkspace'])->name('profile.workspace.update');
    Route::put('/profile/smtp', [ProfileController::class, 'updateSmtp'])->name('profile.smtp');
    Route::match(['post', 'put'], '/profile/smtp/test', [ProfileController::class, 'testSmtp'])
        ->middleware('throttle:5,1')
        ->name('profile.smtp.test');
    Route::delete('/profile/smtp', [ProfileController::class, 'destroySmtp'])->name('profile.smtp.destroy');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::patch('/team/{user}/role', [TeamController::class, 'update'])->name('team.role.update');
    Route::delete('/team/{user}', [TeamController::class, 'destroy'])->name('team.destroy');
    Route::post('/team/invitations', [TeamInvitationController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('team.invitations.store');
    Route::post('/team/invitations/{invitation}/resend', [TeamInvitationController::class, 'resend'])
        ->middleware('throttle:10,1')
        ->name('team.invitations.resend');
    Route::delete('/team/invitations/{invitation}', [TeamInvitationController::class, 'destroy'])
        ->name('team.invitations.destroy');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

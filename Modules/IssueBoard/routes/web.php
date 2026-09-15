<?php

use Illuminate\Support\Facades\Route;
use Modules\IssueBoard\Livewire\Board;
use Modules\IssueBoard\Livewire\IssueDetail;
use Modules\IssueBoard\Livewire\IssueForm;

Route::middleware(config('issueboard.route.middleware'))
    ->prefix(config('issueboard.route.prefix'))
    ->name('issueboard.')
    ->group(function () {
        Route::get('/', Board::class)->name('index');
        Route::get('/new', IssueForm::class)->name('create');
        Route::get('/{issue}', IssueDetail::class)->name('show');
        Route::get('/{issue}/edit', IssueForm::class)->name('edit');
    });

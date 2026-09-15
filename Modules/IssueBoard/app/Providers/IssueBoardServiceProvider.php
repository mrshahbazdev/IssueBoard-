<?php

namespace Modules\IssueBoard\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\IssueBoard\Console\SendIssueDigest;
use Modules\IssueBoard\Livewire\Board;
use Modules\IssueBoard\Livewire\IssueDetail;
use Modules\IssueBoard\Livewire\IssueForm;
use Modules\IssueBoard\Models\Issue;
use Modules\IssueBoard\Policies\IssuePolicy;

class IssueBoardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/issueboard.php', 'issueboard');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'issueboard');
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'issueboard');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        Gate::policy(Issue::class, IssuePolicy::class);

        Livewire::component('issueboard.board', Board::class);
        Livewire::component('issueboard.issue-form', IssueForm::class);
        Livewire::component('issueboard.issue-detail', IssueDetail::class);

        if ($this->app->runningInConsole()) {
            $this->commands([SendIssueDigest::class]);

            $this->publishes([
                __DIR__.'/../../config/issueboard.php' => config_path('issueboard.php'),
            ], 'issueboard-config');

            $this->app->booted(function () {
                if (! config('issueboard.digest.enabled')) {
                    return;
                }

                $schedule = $this->app->make(Schedule::class);
                $schedule->command('issueboard:digest')
                    ->weeklyOn(
                        $this->dayNumber(config('issueboard.digest.day')),
                        config('issueboard.digest.time')
                    );
            });
        }
    }

    protected function dayNumber(string $day): int
    {
        return [
            'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
            'thursday' => 4, 'friday' => 5, 'saturday' => 6,
        ][strtolower($day)] ?? 1;
    }
}

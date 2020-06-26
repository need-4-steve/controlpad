<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\AutoshipProcess::class,
        Commands\AutoshipReminder::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->call('App\Console\Commands\AutoshipReminder@handle')
            ->description('Autoship Renew Notification')
            ->timezone('UTC')
            ->dailyAt('8:00'); // 1:00am MDT, 2:00am MST

        $schedule->call('App\Console\Commands\AutoshipProcess@handle')
            ->description('Autoship Schedule')
            ->timezone('UTC')
            ->dailyAt('8:00'); // 1:00am MDT, 2:00am MST
    }
}

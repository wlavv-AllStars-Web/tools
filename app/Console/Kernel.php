<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ProcessDueVatValidationsCommand::class,
        \App\Console\Commands\AuditShippedOrdersForBackorder::class,
        \App\Console\Commands\RemovePackLinesFromAutoBackorderAudits::class,
    ];
    
    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
    {
        // Order-state classification is owned exclusively by PrestaShop.
        $schedule->command('vat:validate-due --limit=25')->everyMinute()->withoutOverlapping();
        $schedule->command('asd-images:sync')->dailyAt('03:00')->withoutOverlapping();
        $schedule->command('youtube:check-broken-links')->cron('30 4 */3 * *')->withoutOverlapping();
        $schedule->command('newsletter:send-pending --limit=10')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('web:export-products')->dailyAt('01:00')->withoutOverlapping();
        $schedule->command('auto-backorder:audit')->dailyAt(config('auto_backorder.schedule_time', '01:30'))->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

<?php

namespace App\Console;

use App\Console\Commands\AllClear;
use App\Models\Event;
use App\Services\CampaignPricingService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        AllClear::class
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('subscription:check')->when(function (){
            if (addon_is_activated('seller_subscription') && settingHelper('seller_system') == 1) {
                return true;
            }

            return false;
        })->everyFifteenMinutes();

        // Check and expire campaigns every 5 minutes
        $schedule->call(function () {
            Event::where('status', 'active')
                ->where('is_active', 1)
                ->where('event_type', 'date_range')
                ->where('event_schedule_end', '<', Carbon::now())
                ->update([
                    'status' => 'expired',
                    'is_active' => 0,
                    'deactivated_at' => Carbon::now(),
                ]);

            // Clear cache
            if (class_exists(CampaignPricingService::class)) {
                app(CampaignPricingService::class)->clearCache();
            }
        })->everyFiveMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
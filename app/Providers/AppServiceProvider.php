<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Schema::defaultStringLength(191);
        if (Schema::hasTable('languages')) {
            app_config();
        }
        if (settingHelper('current_version') == "170") {
            Setting::where('title', 'current_version')->update(['value' => "180"]);
            Setting::where('title', 'version_code')->update(['value' => "1.8.0"]);
            envWrite('DEMO_MODE', false);
            envWrite('DEV_MODE', false);
            if (isAppMode()) {
                envWrite('MOBILE_MODE', true);
            } else {
                envWrite('MOBILE_MODE', false);
            }
        }
    }
}
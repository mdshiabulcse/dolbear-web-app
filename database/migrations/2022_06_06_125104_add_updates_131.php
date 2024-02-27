<?php

use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdates131 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(0)->after('balance');
        });

        Schema::table('sliders', function (Blueprint $table) {
            $table->string('action_type')->default(null)->after('for_mobile')->index();
        });

        \App\Models\Slider::insert([
            'order' => 1,
            'status' => 1,
            'for_mobile' => 0,
            'action_type' => '',
            'link' => '/',
            'bg_image' => '[]',
            'bg_image_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

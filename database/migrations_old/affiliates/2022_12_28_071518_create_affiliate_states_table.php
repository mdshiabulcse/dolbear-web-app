<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable("affiliate_states")) {
            return;
        }
        Schema::create('affiliate_states', function (Blueprint $table) {
            $table->id();
            $table->integer('affiliate_user_id');
            $table->integer('no_of_click');
            $table->integer('no_of_order_item');
            $table->integer('no_of_delivered');
            $table->integer('no_of_cancel');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_states');
    }
}

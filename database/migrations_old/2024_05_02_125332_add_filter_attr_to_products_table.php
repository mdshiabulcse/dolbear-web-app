<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilterAttrToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->tinyInteger('is_new_arrived')->default(0);
            $table->tinyInteger('is_best_seller')->default(0);
            $table->tinyInteger('is_bundle_deals')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_new_arrived');
            $table->dropColumn('is_best_seller');
            $table->dropColumn('is_bundle_deals');
        });
    }
}

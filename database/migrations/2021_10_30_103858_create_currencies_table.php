<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol');
            $table->string('code');
            $table->double('exchange_rate', 10, 3)->default(1);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
        \App\Models\Currency::insert([
            'name' => 'US Dollar',
            'symbol' => '$',
            'code' => 'USD',
            'exchange_rate' => 1,
            'status' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}

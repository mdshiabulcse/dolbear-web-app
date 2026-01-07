<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThemeOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theme_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('options');
            $table->timestamps();
        });

        \App\Models\ThemeOptions::insert([
            'name' => 'theme_one',
            'options' => '{"header_style":"header_style1","footer_style":"footer_style1","primary_color":"#000000","fonts":"roboto"}',
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
        Schema::dropIfExists('theme_options');
    }
}

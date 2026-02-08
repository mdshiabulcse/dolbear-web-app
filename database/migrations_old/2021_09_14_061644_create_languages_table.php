<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->index();
            $table->string('locale', 30)->unique()->index();
            $table->boolean('status')->default(1);
            $table->string('flag', 50)->nullable();
            $table->string('text_direction', 30)->default('ltr')->nullable();
            $table->timestamps();
        });
        $now = date('Y-m-d H:i:s');

        \App\Models\Language::insert([
            'name' => 'English',
            'locale' => 'en',
            'status' => 1,
            'flag' => 'images/flags/us.png',
            'text_direction' => 'ltr',
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
}

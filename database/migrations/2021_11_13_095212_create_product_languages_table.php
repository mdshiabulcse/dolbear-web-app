<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_languages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned();
            $table->string('lang',10)->default('en');
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->bigInteger('pdf_specification_id')->unsigned()->nullable();
            $table->mediumText('pdf_specification')->nullable();
            $table->string('tags')->nullable();
            $table->string('unit')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamps();
        });
        $now = now();

        \App\Models\ProductLanguage::insert([
            'product_id' => 1,
            'lang' => 'en',
            'name' => 'Demo Product',
            'short_description' => 'Short Description......',
            'description' => '<p>Long Description.......<br></p>',
            'pdf_specification_id' => NULL,
            'pdf_specification' => '[]',
            'tags' => 'demo,demo tag',
            'unit' => 'PCS',
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_languages');
    }
}

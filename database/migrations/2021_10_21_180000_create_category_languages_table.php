<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_languages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('category_id');
            $table->string('lang', 10)->default('en')->comment('our default locale for system en');
            $table->string('title')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable()->comment('meta description for seo');
            $table->timestamps();
        });

        $now = date('Y-m-d H:i:s');

        $category_languages = [
            [
                'category_id' => 1,
                'lang' => 'en',
                'title' => 'Category One',
                'meta_title' => '',
                'meta_description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 2,
                'lang' => 'en',
                'title' => 'Category Two',
                'meta_title' => '',
                'meta_description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 3,
                'lang' => 'en',
                'title' => 'Category Three',
                'meta_title' => '',
                'meta_description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 4,
                'lang' => 'en',
                'title' => 'Category Four',
                'meta_title' => '',
                'meta_description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 5,
                'lang' => 'en',
                'title' => 'Category Five',
                'meta_title' => '',
                'meta_description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 6,
                'lang' => 'en',
                'title' => 'Category Six',
                'meta_title' => '',
                'meta_description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 7,
                'lang' => 'en',
                'title' => 'Category Seven',
                'meta_title' => '',
                'meta_description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 8,
                'lang' => 'en',
                'title' => 'Category Eight',
                'meta_title' => '',
                'meta_description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 9,
                'lang' => 'en',
                'title' => 'Category Nine',
                'meta_title' => '',
                'meta_description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'category_id' => 10,
                'lang' => 'en',
                'title' => 'Category Ten',
                'meta_title' => '',
                'meta_description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        \App\Models\CategoryLanguage::insert($category_languages);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_languages');
    }
}

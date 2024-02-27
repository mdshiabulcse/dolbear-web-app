<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_languages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('page_id')->unsigned();
            $table->string('lang',10)->default('en')->comment('our default locale for system en');
            $table->string('title')->nullable();
            $table->string('address')->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->mediumText('meta_description')->nullable();
            $table->text('keywords')->nullable();
            $table->timestamps();
        });

        $now = date('Y-m-d H:i:s');
        $page_languages = [
            [
                'page_id' => 1,
                'lang' => 'en',
                'title' => 'Seller Policy',
                'address' => NULL,
                'content' => NULL,
                'meta_title' => NULL,
                'meta_description' => NULL,
                'keywords' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'page_id' => 2,
                'lang' => 'en',
                'title' => 'Refund Policy',
                'address' => NULL,
                'content' => NULL,
                'meta_title' => NULL,
                'meta_description' => NULL,
                'keywords' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'page_id' => 3,
                'lang' => 'en',
                'title' => 'Support Policy',
                'address' => NULL,
                'content' => NULL,
                'meta_title' => NULL,
                'meta_description' => NULL,
                'keywords' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'page_id' => 4,
                'lang' => 'en',
                'title' => 'Term and Conditions',
                'address' => NULL,
                'content' => NULL,
                'meta_title' => NULL,
                'meta_description' => NULL,
                'keywords' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'page_id' => 5,
                'lang' => 'en',
                'title' => 'Privacy Policy',
                'address' => NULL,
                'content' => NULL,
                'meta_title' => NULL,
                'meta_description' => NULL,
                'keywords' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'page_id' => 6,
                'lang' => 'en',
                'title' => 'About Us',
                'address' => NULL,
                'content' => NULL,
                'meta_title' => NULL,
                'meta_description' => NULL,
                'keywords' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'page_id' => 7,
                'lang' => 'en',
                'title' => 'Contact Us',
                'address' => NULL,
                'content' => NULL,
                'meta_title' => NULL,
                'meta_description' => NULL,
                'keywords' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];
        \App\Models\PageLanguage::insert($page_languages);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_languages');
    }
}

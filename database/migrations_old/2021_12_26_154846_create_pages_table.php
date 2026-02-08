<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->text('link')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->longText('content')->nullable();
            $table->text('meta_image')->nullable();
            $table->bigInteger('meta_image_id')->nullable();
            $table->timestamps();
        });
        $now = date('Y-m-d H:i:s');
        $pages = [
            [
                'type' => 'seller_policy_pages',
                'link' => 'seller-policy',
                'status' => 1,
                'content' => NULL,
                'meta_image' => '[]',
                'meta_image_id' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'refund_policy_page',
                'link' => 'refund-policy',
                'status' => 1,
                'content' => NULL,
                'meta_image' => '[]',
                'meta_image_id' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'support_policy_page',
                'link' => 'support-policy',
                'status' => 1,
                'content' => NULL,
                'meta_image' => '[]',
                'meta_image_id' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'term_conditions_page',
                'link' => 'terms-conditions',
                'status' => 1,
                'content' => NULL,
                'meta_image' => '[]',
                'meta_image_id' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'privacy_policy_page',
                'link' => 'privacy-policy',
                'status' => 1,
                'content' => NULL,
                'meta_image' => '[]',
                'meta_image_id' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'about_us_page',
                'link' => 'about',
                'status' => 1,
                'content' => NULL,
                'meta_image' => '[]',
                'meta_image_id' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'contact_us_page',
                'link' => 'contact',
                'status' => 1,
                'content' => NULL,
                'meta_image' => '[]',
                'meta_image_id' => NULL,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        \App\Models\Page::insert($pages);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}

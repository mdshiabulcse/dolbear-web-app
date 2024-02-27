<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable();
            $table->integer('position')->nullable();
            $table->string('slug',100);
            $table->double('commission',10,3)->default(0)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('icon')->nullable();
            $table->bigInteger('logo_id')->unsigned()->nullable();
            $table->text('logo')->nullable();
            $table->bigInteger('banner_id')->unsigned()->nullable();
            $table->text('banner')->nullable();
            $table->timestamps();
        });

        $now = date('Y-m-d H:i:s');

        $categories = [
            [
                'parent_id' => NULL,
                'position' => NULL,
                'slug' => 'category-one-kfs0j',
                'commission' => 0.000,
                'status' => 1,
                'icon' => '',
                'logo_id' => 1,
                'logo' => '[]',
                'banner_id' => 1,
                'banner' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => NULL,
                'position' => NULL,
                'slug' => 'category-two-jivat',
                'commission' => 0.000,
                'status' => 1,
                'icon' => '',
                'logo_id' => NULL,
                'logo' => '[]',
                'banner_id' => NULL,
                'banner' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => NULL,
                'position' => NULL,
                'slug' => 'category-three-fhtpw',
                'commission' => 0.000,
                'status' => 1,
                'icon' => '',
                'logo_id' => NULL,
                'logo' => '[]',
                'banner_id' => NULL,
                'banner' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => NULL,
                'position' => NULL,
                'slug' => 'category-four-yn5ad',
                'commission' => 0.000,
                'status' => 1,
                'icon' => '',
                'logo_id' => NULL,
                'logo' => '[]',
                'banner_id' => NULL,
                'banner' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => NULL,
                'position' => NULL,
                'slug' => 'category-five-ndaew',
                'commission' => 0.000,
                'status' => 1,
                'icon' => '',
                'logo_id' => NULL,
                'logo' => '[]',
                'banner_id' => NULL,
                'banner' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => NULL,
                'position' => NULL,
                'slug' => 'category-six-n2bo7',
                'commission' => 0.000,
                'status' => 1,
                'icon' => '',
                'logo_id' => NULL,
                'logo' => '[]',
                'banner_id' => NULL,
                'banner' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => NULL,
                'position' => NULL,
                'slug' => 'category-seven-okccl',
                'commission' => 0.000,
                'status' => 1,
                'icon' => '',
                'logo_id' => NULL,
                'logo' => '[]',
                'banner_id' => NULL,
                'banner' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => NULL,
                'position' => NULL,
                'slug' => 'category-eight-hnuml',
                'commission' => 0.000,
                'status' => 1,
                'icon' => '',
                'logo_id' => NULL,
                'logo' => '[]',
                'banner_id' => NULL,
                'banner' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => NULL,
                'position' => NULL,
                'slug' => 'category-nine-jtrwm',
                'commission' => 0.000,
                'status' => 1,
                'icon' => '',
                'logo_id' => NULL,
                'logo' => '[]',
                'banner_id' => NULL,
                'banner' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => NULL,
                'position' => NULL,
                'slug' => 'category-ten-uihpj',
                'commission' => 0.000,
                'status' => 1,
                'icon' => '',
                'logo_id' => NULL,
                'logo' => '[]',
                'banner_id' => NULL,
                'banner' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        \App\Models\Category::insert($categories);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}

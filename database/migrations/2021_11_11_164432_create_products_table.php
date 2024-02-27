<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('brand_id')->unsigned()->nullable();
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->nullable()->unsigned()->comment('if none or 1 then own else sellers');
            $table->bigInteger('created_by')->unsigned();
            $table->string('slug');

            //price & special price
            $table->double('price',20,3)->comment('actual price of the product')->default(0.00);
            $table->double('special_discount', 20,3)->nullable()->comment('special discount amount')->default(0.00);
            $table->string('special_discount_type')->nullable();
            $table->dateTime('special_discount_start')->nullable();
            $table->dateTime('special_discount_end')->nullable();

            //cost
            $table->double('purchase_cost', 20,3)->default(0.00);

            //product specification
            $table->string('barcode')->nullable();
            $table->string('video_provider')->nullable();
            $table->string('video_url')->nullable();
            $table->string('colors')->nullable();
            $table->string('attribute_sets')->nullable();
            $table->string('vat_taxes')->nullable();
            $table->tinyInteger('has_variant')->default(0);
            $table->mediumText('selected_variants')->nullable();
            $table->mediumText('selected_variants_ids')->nullable();
            $table->text('thumbnail')->nullable();
            $table->text('images')->nullable();
            $table->string('thumbnail_id')->nullable();
            $table->string('image_ids')->nullable();

            //stock & sale
            $table->integer('current_stock');
            $table->integer('minimum_order_quantity')->default(1);
            $table->tinyInteger('stock_notification')->default(0);
            $table->integer('low_stock_to_notify')->nullable();
            $table->enum('stock_visibility', ['hide_stock','visible_with_quantity','visible_with_text'])->default('hide_stock');
            $table->bigInteger('total_sale')->default(0);

            //status and other featured
            $table->enum('status',['unpublished','published','trash'])->default('unpublished')->comment('');
            $table->tinyInteger('is_approved')->default(0)->comment('use for seller product approval purpose');
            $table->tinyInteger('is_catalog')->default(0)->comment("if 1 can't added to cart only view");
            $table->string('external_link')->nullable();
            $table->tinyInteger('is_featured')->default(0);
            $table->tinyInteger('is_classified')->default(0);
            $table->tinyInteger('is_wholesale')->default(0);
            $table->text('contact_info')->nullable();
            $table->tinyInteger('is_digital')->default(0);
            $table->tinyInteger('is_refundable')->default(0);
            $table->tinyInteger('todays_deal')->default(0);
            $table->double('rating',8,2)->default(0.00);
            $table->bigInteger('viewed')->default(0)->comment('total views of the product');

            //shipping
            $table->string('shipping_type')->nullable();
            $table->double('shipping_fee', 20,3)->default(0.00);
            $table->tinyInteger('shipping_fee_depend_on_quantity')->default(0);
            $table->text('estimated_shipping_days')->nullable()->comment('estimated time of delivering the product');
            $table->tinyInteger('cash_on_delivery')->default(0)->comment('0 not available, 1 available');

            $table->text('meta_image')->nullable();
            $table->text('product_file')->nullable();
            $table->unsignedBigInteger('product_file_id')->unsigned()->nullable();
            $table->string('meta_image_id')->nullable();
            $table->double('reward',20,3)->default(0.00);

            $table->tinyInteger('is_deleted')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
        $now = date('Y-m-d H:i:s');
        \App\Models\Product::insert([
            'brand_id' => NULL,
            'category_id' => 1,
            'user_id' => 1,
            'created_by' => 1,
            'slug' => 'demo-product-znkzt',
            'price' => '9',
            'special_discount' => 5,
            'special_discount_type' => 'flat',
            'special_discount_start' => $now,
            'special_discount_end' => now()->addDays(10),
            'purchase_cost' => 0,
            'barcode' => '8Y6M2FWIBSDXEDPL',
            'video_provider' => '',
            'video_url' => '',
            'colors' => '[]',
            'attribute_sets' => '[]',
            'vat_taxes' => '',
            'has_variant' => 0,
            'selected_variants' => '[]',
            'selected_variants_ids' => '[]',
            'thumbnail' => '[]',
            'images' => '[]',
            'thumbnail_id' => NULL,
            'image_ids' => NULL,
            'current_stock' => 200,
            'minimum_order_quantity' => 1,
            'stock_notification' => 1,
            'low_stock_to_notify' => 10,
            'stock_visibility' => 'visible_with_quantity',
            'total_sale' => 0,
            'status' => 'published',
            'is_approved' => 1,
            'is_catalog' => 0,
            'external_link' => NULL,
            'is_featured' => 1,
            'is_classified' => 0,
            'is_wholesale' => 0,
            'contact_info' => '[]',
            'is_digital' => 0,
            'is_refundable' => 0,
            'todays_deal' => 1,
            'rating' => 0,
            'viewed' => 0,
            'shipping_type' => 'flat_rate',
            'shipping_fee' => 10,
            'shipping_fee_depend_on_quantity' => 1,
            'estimated_shipping_days' => '0',
            'cash_on_delivery' => 1,
            'meta_image' => '[]',
            'product_file' => NULL,
            'product_file_id' => NULL,
            'meta_image_id' => NULL,
            'reward' => 0,
            'is_deleted' => 0,
            'deleted_at' => NULL,
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
        Schema::dropIfExists('products');
    }
}

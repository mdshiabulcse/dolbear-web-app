<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('event_id')->unsigned();
            $table->bigInteger('product_id')->unsigned();

            // Event-specific pricing
            $table->double('event_price', 20, 3)->nullable()->comment('Special price for this event (null = use product price)');
            $table->double('discount_amount', 20, 3)->default(0.000)->comment('Discount amount for this event');
            $table->enum('discount_type', ['flat', 'percentage'])->default('flat')->comment('Type of discount: flat or percentage');

            // Priority for product within event (useful for displaying products in order)
            $table->integer('product_priority')->default(0)->comment('Display priority within event (lower = higher priority)');

            // Stock control for event
            $table->integer('event_stock')->nullable()->comment('Limited stock for this event (null = use product stock)');
            $table->integer('event_stock_sold')->default(0)->comment('Stock sold for this event');

            // Status
            $table->tinyInteger('is_active')->default(1)->comment('Is this product active in event (0=No, 1=Yes)');
            $table->enum('status', ['pending', 'active', 'paused', 'sold_out', 'expired'])->default('active')->comment('Product status in event');

            // Additional settings
            $table->text('badge_text')->nullable()->comment('Custom badge text for product in this event');
            $table->string('badge_color')->nullable()->comment('Badge color for product in this event');

            // User tracking
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();

            // Timestamps
            $table->timestamps();

            // Foreign keys
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Indexes
            $table->index('event_id');
            $table->index('product_id');
            $table->index('is_active');
            $table->index('status');
            $table->index('product_priority');

            // Unique constraint: Same product can only be added once per event
            $table->unique(['event_id', 'product_id'], 'unique_event_product');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_products');
    }
}
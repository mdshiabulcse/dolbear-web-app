<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add campaign type support to events table
        Schema::table('events', function (Blueprint $table) {
            // Campaign type support
            $table->enum('campaign_type', ['product', 'category', 'brand', 'event'])->default('product')->after('event_type');

            // Single active enforcement
            $table->timestamp('activated_at')->nullable()->after('is_active');
            $table->timestamp('deactivated_at')->nullable()->after('activated_at');

            // Default discount for inherited products (category/brand campaigns)
            $table->decimal('default_discount', 10, 2)->default(0)->after('event_priority');
            $table->enum('default_discount_type', ['flat', 'percentage'])->default('percentage')->after('default_discount');

            // Badge settings for campaign display
            $table->string('badge_text', 255)->nullable()->after('default_discount_type');
            $table->string('badge_color', 20)->nullable()->after('badge_text');
        });

        // Add inheritance tracking to event_products table
        Schema::table('event_products', function (Blueprint $table) {
            // Inherited from category/brand campaigns
            $table->boolean('is_inherited')->default(false)->after('status');
            $table->unsignedBigInteger('parent_event_id')->nullable()->after('is_inherited');

            // Add final_price column for calculated prices
            $table->decimal('final_price', 10, 2)->nullable()->after('event_price');
        });

        // New table for campaign categories
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('category_id');
            $table->boolean('include_subcategories')->default(true);
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->unique(['event_id', 'category_id']);
        });

        // New table for campaign brands
        Schema::create('event_brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('brand_id');
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->unique(['event_id', 'brand_id']);
        });

        // Add index for single-active constraint
        Schema::table('events', function (Blueprint $table) {
            $table->index(['status', 'is_active', 'activated_at'], 'idx_single_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_brands');
        Schema::dropIfExists('event_categories');

        Schema::table('event_products', function (Blueprint $table) {
            $table->dropColumn(['is_inherited', 'parent_event_id', 'final_price']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('idx_single_active');
            $table->dropColumn(['campaign_type', 'activated_at', 'deactivated_at', 'default_discount', 'default_discount_type', 'badge_text', 'badge_color']);
        });
    }
};

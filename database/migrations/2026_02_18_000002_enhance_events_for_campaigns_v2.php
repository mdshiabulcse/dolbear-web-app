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
        // Add campaign type support to events table (if columns don't exist)
        if (!Schema::hasColumn('events', 'campaign_type')) {
            Schema::table('events', function (Blueprint $table) {
                $table->enum('campaign_type', ['product', 'category', 'brand', 'event'])->default('product')->after('event_type');
            });
        }

        if (!Schema::hasColumn('events', 'activated_at')) {
            Schema::table('events', function (Blueprint $table) {
                $table->timestamp('activated_at')->nullable()->after('is_active');
            });
        }

        if (!Schema::hasColumn('events', 'deactivated_at')) {
            Schema::table('events', function (Blueprint $table) {
                $table->timestamp('deactivated_at')->nullable()->after('activated_at');
            });
        }

        if (!Schema::hasColumn('events', 'default_discount')) {
            Schema::table('events', function (Blueprint $table) {
                $table->decimal('default_discount', 10, 2)->default(0)->after('event_priority');
            });
        }

        if (!Schema::hasColumn('events', 'default_discount_type')) {
            Schema::table('events', function (Blueprint $table) {
                $table->enum('default_discount_type', ['flat', 'percentage'])->default('percentage')->after('default_discount');
            });
        }

        if (!Schema::hasColumn('events', 'badge_text')) {
            Schema::table('events', function (Blueprint $table) {
                $table->string('badge_text', 255)->nullable()->after('default_discount_type');
            });
        }

        if (!Schema::hasColumn('events', 'badge_color')) {
            Schema::table('events', function (Blueprint $table) {
                $table->string('badge_color', 20)->nullable()->after('badge_text');
            });
        }

        // Add inheritance tracking to event_products table (if columns don't exist)
        if (!Schema::hasColumn('event_products', 'is_inherited')) {
            Schema::table('event_products', function (Blueprint $table) {
                $table->boolean('is_inherited')->default(false)->after('status');
            });
        }

        if (!Schema::hasColumn('event_products', 'parent_event_id')) {
            Schema::table('event_products', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_event_id')->nullable()->after('is_inherited');
            });
        }

        if (!Schema::hasColumn('event_products', 'final_price')) {
            Schema::table('event_products', function (Blueprint $table) {
                $table->decimal('final_price', 10, 2)->nullable()->after('event_price');
            });
        }

        // New table for campaign categories (if not exists)
        if (!Schema::hasTable('event_categories')) {
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
        }

        // New table for campaign brands (if not exists)
        if (!Schema::hasTable('event_brands')) {
            Schema::create('event_brands', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('brand_id');
                $table->timestamps();

                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
                $table->unique(['event_id', 'brand_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_brands');
        Schema::dropIfExists('event_categories');

        Schema::table('event_products', function (Blueprint $table) {
            if (Schema::hasColumn('event_products', 'final_price')) {
                $table->dropColumn(['final_price']);
            }
            if (Schema::hasColumn('event_products', 'parent_event_id')) {
                $table->dropColumn(['parent_event_id']);
            }
            if (Schema::hasColumn('event_products', 'is_inherited')) {
                $table->dropColumn(['is_inherited']);
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'badge_color')) {
                $table->dropColumn(['badge_color']);
            }
            if (Schema::hasColumn('events', 'badge_text')) {
                $table->dropColumn(['badge_text']);
            }
            if (Schema::hasColumn('events', 'default_discount_type')) {
                $table->dropColumn(['default_discount_type']);
            }
            if (Schema::hasColumn('events', 'default_discount')) {
                $table->dropColumn(['default_discount']);
            }
            if (Schema::hasColumn('events', 'deactivated_at')) {
                $table->dropColumn(['deactivated_at']);
            }
            if (Schema::hasColumn('events', 'activated_at')) {
                $table->dropColumn(['activated_at']);
            }
            if (Schema::hasColumn('events', 'campaign_type')) {
                $table->dropColumn(['campaign_type']);
            }
        });
    }
};

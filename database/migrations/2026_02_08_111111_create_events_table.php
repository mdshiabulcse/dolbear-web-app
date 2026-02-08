<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('banner_image_id')->nullable();

            // Priority: Lower number = higher priority
            $table->integer('event_priority')->default(0)->comment('Lower number = higher priority, multiple events can run simultaneously based on priority');

            // Event type for scheduling
            $table->enum('event_type', ['date_range', 'daily', 'recurring'])->default('date_range')->comment('date_range: specific date range, daily: every day, recurring: recurring pattern');

            // Date/Time scheduling
            $table->dateTime('event_schedule_start')->nullable()->comment('Event start date and time');
            $table->dateTime('event_schedule_end')->nullable()->comment('Event end date and time');

            // Recurring settings for daily/recurring events
            $table->json('recurring_settings')->nullable()->comment('Store recurring pattern data like days, times, etc.');
            $table->time('daily_start_time')->nullable()->comment('Start time for daily events');
            $table->time('daily_end_time')->nullable()->comment('End time for daily events');

            // Event display settings
            $table->string('background_color')->nullable()->comment('Background color for event display');
            $table->string('text_color')->nullable()->comment('Text color for event display');
            $table->tinyInteger('show_on_frontend')->default(1)->comment('Show event on frontend (0=No, 1=Yes)');

            // Status management
            $table->enum('status', ['draft', 'active', 'paused', 'expired', 'cancelled'])->default('draft')->comment('Event status');
            $table->tinyInteger('is_active')->default(1)->comment('Quick active flag (0=Inactive, 1=Active)');

            // Tracking
            $table->integer('total_products')->default(0)->comment('Total products associated with this event');
            $table->bigInteger('total_views')->default(0)->comment('Total event views');
            $table->bigInteger('total_sales')->default(0)->comment('Total sales from this event');
            $table->double('total_revenue', 20, 3)->default(0.000)->comment('Total revenue generated from this event');

            // User tracking
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();

            // Soft delete and timestamps
            $table->tinyInteger('is_deleted')->default(0);
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('event_type');
            $table->index('event_priority');
            $table->index('event_schedule_start');
            $table->index('event_schedule_end');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
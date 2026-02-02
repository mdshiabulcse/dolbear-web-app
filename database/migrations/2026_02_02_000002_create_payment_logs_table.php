<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->comment('Related order ID');
            $table->string('trx_id')->nullable()->comment('Internal transaction ID');
            $table->string('gateway_tran_id')->nullable()->comment('Payment gateway transaction ID');
            $table->string('order_code')->nullable()->comment('Order code (e.g., #50009)');
            $table->enum('gateway', ['sslcommerz', 'paypal', 'stripe', 'bkash', 'nagad', 'rocket', 'kkiapay', 'other'])->default('sslcommerz')->comment('Payment gateway used');

            // Event type - what triggered this log
            $table->enum('event_type', [
                'initiation',           // Payment initiated
                'redirect',             // Redirected to gateway
                'success_callback',     // Success callback received
                'fail_callback',        // Fail callback received
                'cancel_callback',      // Cancel callback received
                'ipn_received',         // IPN received
                'ipn_processed',        // IPN processed successfully
                'validation_success',   // Transaction validated successfully
                'validation_failed',    // Transaction validation failed
                'order_completed',      // Order completed
                'order_failed',         // Order failed
                'duplicate_prevented',  // Duplicate payment prevented
                'error',                // Any error occurred
            ])->default('initiation')->comment('Type of payment event');

            // Payment status
            $table->enum('payment_status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending')->comment('Payment status');

            // Amount details
            $table->decimal('amount', 10, 2)->nullable()->comment('Payment amount');
            $table->string('currency', 10)->nullable()->comment('Currency code (e.g., BDT, USD)');

            // Gateway response details
            $table->string('val_id')->nullable()->comment('Validation ID from gateway');
            $table->string('card_type')->nullable()->comment('Card/wallet type used');
            $table->string('bank_tran_id')->nullable()->comment('Bank transaction ID');
            $table->string('status')->nullable()->comment('Gateway response status');

            // Request data (JSON)
            $table->json('request_data')->nullable()->comment('Full request data from gateway');
            $table->json('response_data')->nullable()->comment('Full response data from gateway');
            $table->json('validation_data')->nullable()->comment('Validation response data');

            // Error handling
            $table->text('error_message')->nullable()->comment('Error message if any');
            $table->string('error_code')->nullable()->comment('Error code from gateway');

            // HTTP details
            $table->string('ip_address')->nullable()->comment('Client IP address');
            $table->text('user_agent')->nullable()->comment('Client user agent');
            $table->enum('environment', ['sandbox', 'production'])->default('production')->comment('Payment environment');

            // Timing
            $table->timestamp('initiated_at')->nullable()->comment('When payment was initiated');
            $table->timestamp('gateway_responded_at')->nullable()->comment('When gateway responded');
            $table->timestamp('completed_at')->nullable()->comment('When payment was completed');

            // Additional notes
            $table->text('notes')->nullable()->comment('Additional notes or comments');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for faster queries
            $table->index('order_id');
            $table->index('trx_id');
            $table->index('gateway_tran_id');
            $table->index('order_code');
            $table->index('gateway');
            $table->index('event_type');
            $table->index('payment_status');
            $table->index('created_at');

            // Foreign key to orders table
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['order_id']);
            $table->dropIndex(['trx_id']);
            $table->dropIndex(['gateway_tran_id']);
            $table->dropIndex(['order_code']);
            $table->dropIndex(['gateway']);
            $table->dropIndex(['event_type']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['created_at']);
        });

        Schema::dropIfExists('payment_logs');
    }
}
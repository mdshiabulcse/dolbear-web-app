<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGatewayTranIdToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add gateway_tran_id field to store SSLCommerz/other payment gateway transaction ID
            // This is different from trx_id which is our internal transaction reference
            $table->string('gateway_tran_id')->nullable()->after('trx_id')->comment('Payment gateway transaction ID (e.g., SSLCommerz tran_id)');

            // Add index for faster lookups during IPN callbacks
            $table->index('gateway_tran_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['gateway_tran_id']);
            $table->dropColumn('gateway_tran_id');
        });
    }
}
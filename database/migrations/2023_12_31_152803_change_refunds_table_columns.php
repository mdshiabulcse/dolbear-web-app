<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeRefundsTableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('payment_type')->after('refund_amount')->nullable();
            $table->text('payment_details')->after('payment_type')->nullable();
            $table->double('exchange_rate')->after('payment_details')->default(1)->nullable();
        });
        $sql = "ALTER TABLE refunds
                MODIFY COLUMN total_amount double;   
                ALTER TABLE refunds
                MODIFY COLUMN shipping_cost double;
                ALTER TABLE refunds
                MODIFY COLUMN refund_amount double;";
        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

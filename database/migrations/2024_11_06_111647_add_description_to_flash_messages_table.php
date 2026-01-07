<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToFlashMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('flash_messages', function (Blueprint $table) {
            $table->renameColumn('message', 'name');
            $table->text('description')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('flash_messages', function (Blueprint $table) {
            $table->renameColumn('name', 'message');
            $table->dropColumn('description');
        });
    }
}

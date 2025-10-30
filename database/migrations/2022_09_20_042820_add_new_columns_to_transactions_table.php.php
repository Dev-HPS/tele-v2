<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('ticket_number', '50');
            $table->integer('sort');
            $table->string('outlet_code', '50');
            $table->string('outlet_name', '100');
            $table->text('outlet_address');
            $table->string('outlet_owner', '50');
            $table->string('outlet_phone', '50')->nullable();
            $table->string('outlet_longitude', '50')->nullable();
            $table->string('outlet_latitude', '50')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['ticket_number']);
            $table->dropColumn(['sort']);
            $table->dropColumn(['outlet_code']);
            $table->dropColumn(['outlet_name']);
            $table->dropColumn(['outlet_address']);
            $table->dropColumn(['outlet_owner']);
            $table->dropColumn(['outlet_phone']);
            $table->dropColumn(['outlet_longitude']);
            $table->dropColumn(['outlet_latitude']);
        });
    }
};
